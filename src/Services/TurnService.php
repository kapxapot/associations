<?php

namespace App\Services;

use App\Events\NewTurnEvent;
use App\Models\Game;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Date;

class TurnService
{
    /** @var EventDispatcher */
    private $dispatcher;

    /** @var AssociationService */
    private $associationService;

    /** @var GameService */
    private $gameService;

    public function __construct(
        EventDispatcher $dispatcher,
        AssociationService $associationService,
        GameService $gameService
    )
    {
        $this->dispatcher = $dispatcher;
        $this->associationService = $associationService;
        $this->gameService = $gameService;
    }

    /**
     * Returns true on success.
     */
    public function finishTurn(Turn $turn, $finishDate = null) : bool
    {
        if ($turn->isFinished()) {
            return false;
        }
        
        $turn->finishedAt = $finishDate ?? Date::dbNow();
        $turn->save();

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
    
    private function newTurn(Game $game, Word $word, User $user = null) : Turn
    {
        $language = $game->language();

        $turn = Turn::create();
        $turn->gameId = $game->getId();
        $turn->languageId = $language->getId();
        
        if ($user !== null) {
            $turn->userId = $user->getId();
        }
        
        $turn->wordId = $word->getId();
        $prevTurn = $game->lastTurn();

        if ($prevTurn !== null) {
            $turn->prevTurnId = $prevTurn->getId();

            $prevWord = $prevTurn->word();
            $association = $this->associationService->getOrCreate($prevWord, $word, $user, $language);

            $turn->associationId = $association->getId();
        }
        
        return $turn->save();
    }
    
    public function processAiTurn(Turn $turn) : void
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->finishTurn(
                $turn->prev(),
                $turn->createdAt
            );
        }
    }
    
    public function processPlayerTurn(Turn $turn) : void
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->finishTurn(
                $turn->prev(),
                $turn->createdAt
            );
        }
        
        // AI next turn
        $game = $turn->game();
        $word = $this->findAnswer($turn);

        if ($word !== null) {
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
        $game->save();

        if ($game->lastTurn() !== null) {
            return $this->finishTurn(
                $game->lastTurn(),
                $game->finishedAt
            );
        }

        return true;
    }

    public function validatePlayerTurn(Game $game, string $wordStr) : bool
    {
        return !$game->containsWordStr($wordStr);
    }

    public function findAnswer(Turn $turn) : ?Word
    {
        $game = $turn->game();
        $user = $turn->user();

        return $turn
            ->word()
            ->associatedWords($user)
            ->where(
                function (Word $word) use ($game) {
                    return !$game->containsWord($word);
                }
            )
            ->random();
    }
}
