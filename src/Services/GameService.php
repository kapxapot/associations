<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Models\Association;
use App\Models\Game;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;

class GameService extends Contained
{
    public function start(Game $game)
    {
        if ($game === null) {
            throw new \InvalidArgumentException('Game cannot be null.');
        }
        
        // already started
        if ($game->turns()->any()) {
            return null;
        }
        
        $language = $game->language();
        $user = $game->user();
        
        // if language has words, AI goes first
        // otherwise player goes first
        $word = $this->languageService->getRandomWordForUser($language, $user);
        
        return ($word !== null)
            ? $this->newAiTurn($game, $word)
            : null;
    }

    public function finish(Game $game)
    {
        $game->finishedAt = Date::dbNow();
        $game->save();
        
        if ($game->lastTurn() !== null) {
            $this->turnService->finish($game->lastTurn(), $game->finishedAt);
        }
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
    
    public function processAiTurn(Turn $turn)
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->turnService->finish($turn->prev(), $turn->createdAt);
        }
    }
    
    public function processPlayerTurn(Turn $turn)
    {
        // finish prev turn
        if ($turn->prev() !== null) {
            $this->turnService->finish($turn->prev(), $turn->createdAt);
        }
        
        // AI next turn
        $game = $turn->game();
        $word = $this->associationService->findAnswer($turn);

        if ($word !== null) {
            $this->newAiTurn($game, $word);
        }
        else {
            $this->finish($game);
        }
    }
    
    public function validatePlayerTurn(Game $game, string $word) : bool
    {
        $lastTurnWord = $game->lastTurnWord();
        $beforeLastTurnWord = $game->beforeLastTurnWord();
        
        $language = $game->language();
        
        $word = $this->languageService->normalizeWord($language, $word);

        return ($lastTurnWord === null || $lastTurnWord->word !== $word) &&
            ($beforeLastTurnWord === null || $beforeLastTurnWord->word !== $word);
    }
}
