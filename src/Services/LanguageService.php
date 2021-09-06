<?php

namespace App\Services;

use App\Collections\WordCollection;
use App\Models\DTO\GameOptions;
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

    public function getDefaultLanguage(): Language
    {
        $defaultId = $this->settingsProvider
            ->get('languages.default_id');

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

    public function getCurrentLanguageFor(?User $user): Language
    {
        $game = $user
            ? $user->currentGame() ?? $user->lastGame()
            : null;

        return $game
            ? $game->language()
            : $this->getDefaultLanguage();
    }

    public function getRandomPublicWord(
        ?Language $language = null,
        ?Word $exceptWord = null
    ): ?Word
    {
        return $this->getRandomWordFor(null, $language, $exceptWord);
    }

    /**
     * Returns **canonical** random word available for the user.
     *
     * - Prioritizes common and public words, if available.
     * - If they are absent, returns any of user's words.
     * - Otherwise, returns any word.
     */
    public function getRandomWordFor(
        ?User $user,
        ?Language $language = null,
        ?Word $exceptWord = null
    ): ?Word
    {
        // 1. try find a common word
        // 2. try find a public word
        // 3. select one of the user's words
        // 4. any word
        $sources = [
            fn () => $this->wordRepository->getAllByScope(Scope::COMMON, $language),
            fn () => $this->wordRepository->getAllByScope(Scope::PUBLIC, $language),
            fn () => $this->wordService->getAllUsedBy($user, $language),
            fn () => $this->wordRepository->getAllByLanguage($language)
        ];

        foreach ($sources as $source) {
            $words = ($source)();
            $word = $this->extractRandomWord($words, $user, $exceptWord);

            if ($word !== null) {
                return $word;
            }
        }

        return null;
    }

    private function extractRandomWord(
        WordCollection $words,
        ?User $user,
        ?Word $exceptWord = null
    ): ?Word
    {
        if ($exceptWord !== null) {
            $words = $words->except($exceptWord);
        }

        $options = new GameOptions();
        $options->isGameStart = true;

        /** @var Word $word */
        $word = $words
            ->shuffle()
            ->first(
                fn (Word $w) => $w->isPlayableAgainst($user, $options)
            );

        return $word !== null
            ? $word->canonicalPlayableAgainst($user)
            : null;
    }

    public function normalizeWord(Language $language, ?string $word) : ?string
    {
        // language is ignored currently
        return $this->wordService->normalize($word);
    }
}
