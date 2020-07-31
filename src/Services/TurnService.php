<?php

namespace App\Services;

use App\Collections\TurnCollection;
use App\Events\Turn\TurnCreatedEvent;
use App\Exceptions\DuplicateWordException;
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
    private EventDispatcher $eventDispatcher;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        AssociationService $associationService,
        EventDispatcher $eventDispatcher
    )
    {
        $this->gameRepository = $gameRepository;
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;
        $this->associationService = $associationService;
        $this->eventDispatcher = $eventDispatcher;
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

    /**
     * Returns new player turn and AI turn/answer if it happens.
     */
    public function newPlayerTurn(Game $game, Word $word, User $user) : TurnCollection
    {
        $turn = $this->newTurn($game, $word, $user);

        $event = new TurnCreatedEvent($turn);
        $this->eventDispatcher->dispatch($event);

        $turns = [$turn];

        $aiTurn = $this->processPlayerTurn($turn);

        if ($aiTurn) {
            $turns[] = $aiTurn;
        }

        return TurnCollection::make($turns);
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

        $turn = $this->turnRepository->save($turn);

        // todo: this relation must be updated by repositories (+ entity manager)
        $this->gameRepository->save($game);

        return $turn;
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

    /**
     * Returns AI turn in answer to player turn (if any).
     */
    public function processPlayerTurn(Turn $turn) : ?Turn
    {
        $this->finishPrevTurn($turn);

        return $this->nextAiTurn($turn);
    }

    private function nextAiTurn(Turn $turn) : ?Turn
    {
        $game = $turn->game();
        $word = $this->findAnswer($turn);

        if ($word) {
            return $this->newAiTurn($game, $word);
        }

        $this->finishGame($game);

        return null;
    }

    /**
     * Returns true on success.
     * 
     * Todo: this should belong to GameService, but creates a circular dependency
     */
    public function finishGame(Game $game) : bool
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
     * Validates player turn.
     * 
     * Normalized word string expected.
     * 
     * @throws DuplicateWordException
     */
    public function validatePlayerTurn(Game $game, string $wordStr) : void
    {
        $language = $game->language();

        $word = $this
            ->wordRepository
            ->findInLanguage($language, $wordStr);

        // unknown yet word
        if (is_null($word)) {
            return;
        }

        if ($game->containsWord($word)) {
            throw new DuplicateWordException($word->word);
        };
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
