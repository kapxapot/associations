<?php

namespace App\Services;

use App\Collections\TurnCollection;
use App\Collections\WordCollection;
use App\Exceptions\TurnException;
use App\Models\DTO\PseudoTurn;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Arrays;
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
    public function getOrCreateNewGameFor(User $user): Game
    {
        return $user->currentGame() ?? $this->createNewGameFor($user);
    }

    /**
     * Creates a new game for the user taking into account the last game.
     *
     * Makes sure that the last word of the last game is not used
     * as a starting word in a new game.
     */
    public function createNewGameFor(User $user): Game
    {
        $lastGame = $user->lastGame();

        $lastWord = $lastGame
            ? $lastGame->lastTurn()->word()
            : null;

        return $this->createGameFor(
            $user,
            $this->getUserLanguage($user),
            $lastWord
        );
    }

    /**
     * Creates and starts a new game for the user.
     *
     * @param Word|null $exceptWord If provided, the algorithm tries not to start a game from this word.
     * But if it's the only word in its language, it can be used anyway.
     */
    public function createGameFor(User $user, ?Language $language = null, ?Word $exceptWord = null): Game
    {
        $language ??= $this->getUserLanguage($user);

        $game = $this->gameRepository->store([
            'language_id' => $language->getId(),
            'user_id' => $user->getId(),
            'created_by' => $user->getId(),
        ]);

        $this->startGame($game, $exceptWord);

        return $game;
    }

    private function getUserLanguage(User $user): Language
    {
        return $this->languageService->getCurrentLanguageFor($user);
    }

    public function startGame(Game $game, ?Word $exceptWord = null): ?Turn
    {
        $language = $game->language();
        $user = $game->user();

        // if language has words, AI goes first
        // otherwise player goes first
        $word = $this->languageService->getRandomStartingWord($language, $exceptWord, $user);

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
    public function makeTurn(
        User $user,
        Game $game,
        ?string $wordStr,
        ?string $originalUtterance = null
    ): TurnCollection
    {
        $language = $game->language();

        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        $originalUtterance = $this->languageService->normalizeWord(
            $language,
            $originalUtterance
        );

        $this->wordService->validateWord($wordStr);
        $this->turnService->validatePlayerTurn($game, $wordStr);

        // get or CREATE word
        $word = $this->wordService->getOrCreate($user, $language, $wordStr, $originalUtterance);

        // new turn (+ AI's potential answer)
        $turns = $this->turnService->newPlayerTurn($user, $game, $word, $originalUtterance);

        Assert::minCount($turns, 1);

        return $turns;
    }

    /**
     * Constructs an ethereal game from the provided words and plays a preudo turn.
     *
     * When there is no answer (for some reason), chooses a random starting word.
     * (Starts a "new game").
     */
    public function playPseudoTurn(Language $language, ?Word $word, ?Word $prevWord = null): PseudoTurn
    {
        if ($word) {
            $game = $this->buildEtherealGame($prevWord, $word);

            $answerTurn = $word
                ? $this->turnService->findAnswer($game, $word)
                : null;

            if ($answerTurn) {
                return $answerTurn;
            }
        }

        $answer = $this->languageService->getRandomStartingWord($language, $word);

        return PseudoTurn::new($answer);
    }

    /**
     * Builds an `ethereal` game with the words provided.
     *
     * `null` words are ignored.
     *
     * @param array<Word|null> $words Game words in ASC order (!).
     */
    public function buildEtherealGame(?Word ...$words): Game
    {
        $words = Arrays::clean($words);

        $turns = TurnCollection::from(
            WordCollection::make($words)
                ->reverse()
                ->map(
                    fn (Word $w) => (new Turn())->withWord($w)
                )
        );

        return (new Game())->withTurns($turns);
    }
}
