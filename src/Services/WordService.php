<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Util\Strings;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;

class WordService extends Contained
{
    public function normalize($word)
    {
        return Strings::normalize($word);
    }

    /**
     * Creates new word.
     * 
     * Word should be normalized in advance!
     * 
     * !!!!!!!!!!!!!!!!!!!
     * Same problem as with duplicate association
     * Two users can add the same word in parallel
     * !!!!!!!!!!!!!!!!!!!
     */
    public function create(Language $language, string $wordStr, User $user)
    {
        if ($language === null) {
            throw new \InvalidArgumentException('Language must be non-null.');
        }
        
        if (strlen($wordStr) === 0) {
            throw new \InvalidArgumentException('Word can\'t be empty.');
        }

        if ($user === null) {
            throw new \InvalidArgumentException('User must be non-null.');
        }
        
        if (Word::findInLanguage($language, $wordStr) !== null) {
            throw new ApplicationException('Word already exists.');
        }
        
        $word = Word::create();
        
        $word->languageId = $language->getId();
        $word->word = $wordStr;
        $word->wordBin = $wordStr;
        $word->createdBy = $user->getId();

        return $word->save();
    }
}
