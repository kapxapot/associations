<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Collection;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;

class LanguageService extends Contained
{
    public function getDefaultLanguage()
    {
        return Language::get($this->getSettings('languages.default_id'));
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
        
        /*if ($language->getId() == Language::RUSSIAN) {
            return str_replace('ё', 'е', $word);
        }*/
        
        return $word;
    }
}
