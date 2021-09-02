<?php

namespace App\Services;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
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
     */
    public function getRandomWordFor(
        ?User $user,
        ?Language $language = null,
        ?Word $exceptWord = null
    ): ?Word
    {
        // get common words
        $words = $this->wordRepository->getAllApproved($language);

        if ($user) {
            // get user's words
            $userWords = $this->wordService->getAllUsedBy($user, $language);

            // union them & distinct
            $words = $words
                ->concat($userWords)
                ->distinct();
        }

        if ($exceptWord !== null) {
            $words = $words->where(
                fn (Word $w) => !$w->equals($exceptWord)
            );
        }

        $word = $words
            ->shuffle()
            ->first(
                fn (Word $w) => $w->isPlayableAgainst($user)
            );

        return $word->canonicalPlayableAgainst($user);
    }

    public function normalizeWord(Language $language, ?string $word) : ?string
    {
        // language is ignored currently
        return $this->wordService->normalize($word);
    }
}
