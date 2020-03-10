<?php

namespace App\Services;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use Plasticode\Collection;
use Plasticode\Interfaces\SettingsProviderInterface;

class LanguageService
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var WordService */
    private $wordService;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        WordService $wordService
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->wordService = $wordService;
    }

    public function getDefaultLanguage()
    {
        $defaultId = $this->settingsProvider->getSettings('languages.default_id')

        return Language::get($defaultId);
    }
    
    public function getRandomWord(Language $language, Collection $exclude = null)
    {
        $words = $language->words();
        
        if ($exclude !== null && $exclude->any()) {
            $words = $words->whereNotIn('id', $exclude->ids());
        }
        
        return $words->random();
    }
    
    public function getRandomWordForUser(Language $language, User $user, Collection $exclude = null)
    {
        // get common words
        $approvedWords = Word::getApproved($language)->all();

        // get user's words
        $userWords = $user->usedWords($language);
        
        // union them & distinct
        $words = Collection::merge($approvedWords, $userWords)
            ->distinct()
            ->where(function ($word) use ($user) {
                return $word->isPlayableAgainstUser($user);
            });

        if ($exclude !== null && $exclude->any()) {
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
