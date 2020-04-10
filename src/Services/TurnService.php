<?php

namespace App\Services;

use App\Events\NewTurnEvent;
use App\Models\Game;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Date;

class TurnService
{
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;

    private AssociationService $associationService;
    private EventDispatcher $dispatcher;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        AssociationService $associationService,
        EventDispatcher $dispatcher
    )
    {
        $this->gameRepository = $gameRepository;
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;
        $this->associationService = $associationService;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns true on success.
     */
    public function finishTurn(Turn $turn, ?string $finishDate = null) : bool
    {
        if ($turn->isFinished()) {
            return true;
        }

        $turn->finishedAt = $finishDate ?? Date::dbNow();

        $this->turnRepository->save($turn);

        return true;
    }

    public function newPlayerTurn(Game $game, Word $word, User $user) : Turn
    {
        $turn = $this->newTurn($game, $word, $user);

        $event = new NewTurnEvent($turn);
        $this->dispatcher->dispatch($event);

        $this->processPlayerTurn($turn);

        return $turn;
    }

    public function newAiTurn(Game $game, Word $word) : Turn
    {
        $turn = $this->newTurn($game, $word);

        $this->processAiTurn($turn);

        return $turn;
    }

    private function newTurn(Game $game, Word $word, ?User $user = null) : Turn
    {
        $language = $game->language();

        $turn = Turn::create();

        $turn->gameId = $game->getId();
        $turn->languageId = $language->getId();

        if ($user) {
            $turn->userId = $user->getId();
        }

        $turn->wordId = $word->getId();
        $prevTurn = $game->lastTurn();

        if ($prevTurn !== null) {
            $turn->prevTurnId = $prevTurn->getId();

            $prevWord = $prevTurn->word();

            $association = $this->associationService->getOrCreate(
                $prevWord,
                $word,
                $user,
                $language
            );

            $turn->associationId = $association->getId();
        }

        return $this->turnRepository->save($turn);
    }

    public function processAiTurn(Turn $turn) : void
    {
        $this->finishPrevTurn($turn);
    }

    private function finishPrevTurn(Turn $turn) : void
    {
        if ($turn->prev()) {
            $this->finishTurn(
                $turn->prev(),
                $turn->createdAt
            );
        }
    }

    public function processPlayerTurn(Turn $turn) : void
    {
        $this->finishPrevTurn($turn);
        $this->nextAiTurn($turn);
    }

    private function nextAiTurn(Turn $turn) : void
    {
        $game = $turn->game();
        $word = $this->findAnswer($turn);

        if ($word) {
            $this->newAiTurn($game, $word);
        } else {
            $this->finishGame($game);
        }
    }

    /**
     * Returns true on success.
     * 
     * Todo: this should belong to GameService, but creates a circular dependency
     */
    private function finishGame(Game $game) : bool
    {
        if ($game->isFinished()) {
            return false;
        }

        $game->finishedAt = Date::dbNow();

        $this->gameRepository->save($game);

        if ($game->lastTurn()) {
            return $this->finishTurn(
                $game->lastTurn(),
                $game->finishedAt
            );
        }

        return true;
    }

    /**
     * Normalized word string expected.
     */
    public function validatePlayerTurn(Game $game, string $wordStr) : bool
    {
        $word = $this
            ->wordRepository
            ->findInLanguage($this->language, $wordStr);

        // unknown yet word
        if (is_null($word)) {
            return true;
        }

        return !$game->containsWord($word);
    }

    public function findAnswer(Turn $turn) : ?Word
    {
        $game = $turn->game();
        $user = $turn->user();

        return $turn
            ->word()
            ->associatedWordsFor($user)
            ->where(
                fn (Word $w) => !$game->containsWord($w)
            )
            ->random();
    }
}
