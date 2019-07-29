<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use Plasticode\Contained;
use Plasticode\Util\Date;

class GameService extends Contained
{
    /**
     * Creates and starts new game
     */
    public function newGame(Language $language, User $user) : Game
    {
        $game = $this->createGame($language, $user);
        $this->startGame($game);

        return $game;
    }

    public function createGame(Language $language, User $user) : Game
    {
        // potentially can create several games in parallel
        $game = Game::create();

        $game->languageId = $language->getId();
        $game->userId = $user->getId();

        return $game->save();
    }

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
     * Returns true on success
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

    /**
     * Returns true, if the provided turn is the last turn of the game
     * OR turn is null and game contains no turns
     */
    public function validateLastTurn(Game $game, Turn $turn) : bool
    {
        $lastTurn = $game->lastTurn();

        return
            ($lastTurn !== null && $turn !== null && $lastTurn->getId() === $turn->getId()) ||
            ($lastTurn === null && $turn === null);
    }
}
