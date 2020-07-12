<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Webmozart\Assert\Assert;

class GameService
{
    private GameRepositoryInterface $gameRepository;

    private LanguageService $languageService;
    private TurnService $turnService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        LanguageService $languageService,
        TurnService $turnService
    )
    {
        $this->gameRepository = $gameRepository;

        $this->languageService = $languageService;
        $this->turnService = $turnService;
    }

    /**
     * Creates and starts a new game.
     */
    public function newGame(Language $language, User $user) : Game
    {
        $game = $this->createGame($language, $user);
        $this->startGame($game);

        return $game;
    }

    public function createGame(Language $language, User $user) : Game
    {
        // todo: potentially can create several games in parallel
        return $this->gameRepository->store(
            [
                'language_id' => $language->getId(),
                'user_id' => $user->getId(),
            ]
        );
    }

    public function startGame(Game $game) : ?Turn
    {
        Assert::notNull($game, 'Game can\'t be null.');

        // already started
        if ($game->isStarted()) {
            return null;
        }

        $language = $game->language();
        $user = $game->user();

        // if language has words, AI goes first
        // otherwise player goes first
        $word = $this->languageService->getRandomWordFor($user, $language);

        return $word
            ? $this->turnService->newAiTurn($game, $word)
            : null;
    }

    /**
     * Returns true, if the provided turn is the last turn of the game
     * OR turn is null and game contains no turns.
     */
    public function validateLastTurn(Game $game, Turn $turn) : bool
    {
        $lastTurn = $game->lastTurn();

        return
            ($lastTurn && $lastTurn->equals($turn))
            || (is_null($lastTurn) && is_null($turn));
    }
}
