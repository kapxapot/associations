<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Models\Game;
use App\Models\Turn;

class GameService extends Contained
{
    public function startGame(Game $game) : ?Turn
    {
        if ($game === null) {
            throw new \InvalidArgumentException('Game can\'t be null.');
        }
        
        // already started
        if ($game->isStarted()) {
            return null;
        }
        
        $language = $game->language();
        $user = $game->user();
        
        // if language has words, AI goes first
        // otherwise player goes first
        $word = $this->languageService->getRandomWordForUser($language, $user);
        
        return ($word !== null)
            ? $this->turnService->newAiTurn($game, $word)
            : null;
    }

    /**
     * Returns true on success.
     */
    public function finishGame(Game $game) : bool
    {
        if ($game->isFinished()) {
            return false;
        }

        $game->finishedAt = Date::dbNow();
        $game->save();

        if ($game->lastTurn() !== null) {
            return $this->turnService->finishTurn($game->lastTurn(), $game->finishedAt);
        }

        return true;
    }
}
