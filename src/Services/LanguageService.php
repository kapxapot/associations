<?php

namespace App\Services;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
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

    public function getDefaultLanguage() : Language
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

    public function getRandomWordFor(
        User $user,
        ?Language $language = null,
        ?Collection $exclude = null
    ) : ?Word
    {
        // get common words
        $approvedWords = $this->wordRepository->getAllApproved($language);

        // get user's words
        $userWords = $this->wordService->getAllUsedBy($user, $language);
        
        // union them & distinct
        $words = $approvedWords
            ->concat($userWords)
            ->distinct()
            ->where(
                fn (Word $w) => $w->isPlayableAgainst($user)
            );

        if ($exclude && $exclude->any()) {
            $words = $words->whereNotIn('id', $exclude->ids());
        }
        
        return $words->random();
    }

    public function normalizeWord(Language $language, string $word) : string
    {
        $word = $this->wordService->normalize($word);
        
        return $word;
    }
}
