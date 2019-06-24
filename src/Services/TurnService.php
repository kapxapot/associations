<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Models\Association;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;

class TurnService extends Contained
{
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
        $turn = Turn::create();
        
        $turn->gameId = $game->getId();
        $turn->languageId = $game->languageId;
        
        if ($user !== null) {
            $turn->userId = $user->getId();
        }
        
        $turn->wordId = $word->getId();

        $prevTurn = $game->lastTurn();

        if ($prevTurn !== null) {
            $turn->prevTurnId = $prevTurn->getId();
            
            $prevWord = $prevTurn->word();
            
            $association = Association::getByPair($prevWord, $word)
                ?? $this->associationService->create($prevWord, $word, $user, $word->language());

            if ($association !== null) {
                $turn->associationId = $association->getId();
            }
        }
        
        return $turn->save();
    }
    
    public function processAiTurn(Turn $turn) : void
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->finishTurn($turn->prev(), $turn->createdAt);
        }
    }
    
    public function processPlayerTurn(Turn $turn) : void
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->finishTurn($turn->prev(), $turn->createdAt);
        }
        
        // AI next turn
        $game = $turn->game();
        $word = $this->findAnswer($turn);

        if ($word !== null) {
            $this->newAiTurn($game, $word);
        }
        else {
            $this->gameService->finishGame($game);
        }
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
            ->where(function ($word) use ($game) {
                return !$game->containsWord($word);
            })
            ->random();
    }
}
