<?php

namespace App\Services;

use App\Collections\WordCollection;
use App\Models\DTO\GameOptions;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Scope;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Webmozart\Assert\Assert;

class LanguageService
{
    private LanguageRepositoryInterface $languageRepository;
    private WordRepositoryInterface $wordRepository;

    private SettingsProviderInterface $settingsProvider;

    private WordService $wordService;

    public function __construct(
        LanguageRepositoryInterface $languageRepository,
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        WordService $wordService
    )
    {
        $this->languageRepository = $languageRepository;
        $this->wordRepository = $wordRepository;

        $this->settingsProvider = $settingsProvider;

        $this->wordService = $wordService;
    }

    /**
     * If the game is not `null`, returns its language.
     * Otherwise returns the default language.
     */
    public function getLanguageByGame(?Game $game): Language
    {
        return $game
            ? $game->language()
            : $this->getDefaultLanguage();
    }

    public function getDefaultLanguage(): Language
    {
        $defaultId = $this->settingsProvider->get('languages.default_id');

        $language = $this->languageRepository->get($defaultId);

        Assert::notNull(
            $language,
            'No default language specified.'
        );

        return $language;
    }

    public function findWord(Language $language, ?string $wordStr): ?Word
    {
        $wordStr = $this->normalizeWord($language, $wordStr);

        return $this->wordRepository->findInLanguage($language, $wordStr);
    }

    /**
     * Returns user's language based on their current or last game.
     * If there is none, returns the default one.
     */
    public function getCurrentLanguageFor(?User $user): Language
    {
        $game = $user
            ? $user->currentGame() ?? $user->lastGame()
            : null;

        return $this->getLanguageByGame($game);
    }

    /**
     * Returns **canonical** random word available for the user to start the game.
     *
     * - Prioritizes common and public words, if available.
     * - If they are absent, returns any of user's words.
     * - Otherwise, returns any word.
     *
     * @param Word|null $exceptWord If provided, the algorithm tries to find another word.
     * But if it's the only word in the language, it can be used.
     */
    public function getRandomStartingWord(
        Language $language,
        ?Word $exceptWord,
        ?User $user = null
    ): ?Word
    {
        $except = fn (WordCollection $words) => $exceptWord
            ? $words->except($exceptWord)
            : $words;

        // 1. try find a common word
        // 2. try find a public word
        // 3. select one of the user's words
        // 4. try find a private word
        $sources = [
            fn () => ($except)($this->wordRepository->getAllByScope(Scope::COMMON, $language)),
            fn () => ($except)($this->wordRepository->getAllByScope(Scope::PUBLIC, $language)),
            fn () => ($except)($this->wordService->getAllUsedBy($user, $language)),
            fn () => ($except)($this->wordRepository->getAllByScope(Scope::PRIVATE, $language))
        ];

        // 5. except word as a failsafe
        if ($exceptWord) {
            $sources[] = fn () => WordCollection::collect($exceptWord);
        }

        $options = (new GameOptions())->asGameStart();

        foreach ($sources as $source) {
            $words = ($source)();
            $word = $this->retrieveSuitableWord($words, $user, $options);

            if ($word) {
                return $word;
            }
        }

        return null;
    }

    /**
     * Looks for a suitable word in the collection.
     *
     * Tries to return a canonical word playable against the user.
     */
    private function retrieveSuitableWord(WordCollection $words, ?User $user, ?GameOptions $options): ?Word
    {
        /** @var Word|null $word */
        $word = $words
            ->shuffle()
            ->first(
                fn (Word $w) => $w->isPlayableAgainst($user, $options)
            );

        return $word
            ? $word->canonicalPlayableAgainst($user)
            : null;
    }

    /**
     * @param Language $language Currently ignored and not used.
     */
    public function normalizeWord(Language $language, ?string $word): ?string
    {
        return $this->wordService->normalize($word);
    }
}
