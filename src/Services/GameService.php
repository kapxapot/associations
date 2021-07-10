<?php

namespace App\Services;

use App\Collections\TurnCollection;
use App\Exceptions\TurnException;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Exceptions\ValidationException;
use Webmozart\Assert\Assert;

class GameService
{
    private GameRepositoryInterface $gameRepository;

    private LanguageService $languageService;
    private TurnService $turnService;
    private WordService $wordService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        LanguageService $languageService,
        TurnService $turnService,
        WordService $wordService
    )
    {
        $this->gameRepository = $gameRepository;

        $this->languageService = $languageService;
        $this->turnService = $turnService;
        $this->wordService = $wordService;
    }

    /**
     * Returns user's current game or creates and starts a new one for them.
     */
    public function getOrCreateGameFor(User $user): Game
    {
        return $user->currentGame() ?? $this->createGameFor($user);
    }

    /**
     * Creates and starts a new game for the user.
     */
    public function createGameFor(User $user, ?Language $language = null): Game
    {
        $language ??= $this->languageService->getCurrentLanguageFor($user);

        $game = $this->gameRepository->store(
            [
                'language_id' => $language->getId(),
                'user_id' => $user->getId(),
                'created_by' => $user->getId(),
            ]
        );

        $this->startGame($game);

        return $game;
    }

    public function startGame(Game $game): ?Turn
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
     * OR turn is `null` and game contains no turns.
     */
    public function validateLastTurn(Game $game, ?Turn $turn): bool
    {
        $lastTurn = $game->lastTurn();

        return $lastTurn !== null
            ? $lastTurn->equals($turn)
            : $turn === null;
    }

    /**
     * User says a word in the game (makes a turn).
     * The result is the user's turn and AI's turn (if the AI has something to say).
     * 
     * @throws ValidationException
     * @throws TurnException
     */
    public function makeTurn(User $user, Game $game, ?string $wordStr): TurnCollection
    {
        $language = $game->language();

        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        $this->wordService->validateWord($wordStr);
        $this->turnService->validatePlayerTurn($game, $wordStr);

        // get or CREATE word
        $word = $this->wordService->getOrCreate($language, $wordStr, $user);

        // new turn (+ AI's potential answer)
        $turns = $this->turnService->newPlayerTurn($game, $word, $user);

        Assert::minCount($turns, 1);

        return $turns;
    }
}
