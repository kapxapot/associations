<?php

namespace App\Services;

use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use Plasticode\Contained;
use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Strings;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class WordService extends Contained
{
    /**
     * Normalized word string expected
     */
    public function getOrCreate(Language $language, string $wordStr, User $user) : Word
    {
        $word =
            Word::findInLanguage($language, $wordStr)
            ??
            $this->create($language, $wordStr, $user);

        if ($word === null) {
            throw new InvalidResultException('Word can\'t be found or added.');
        }
    
        return $word;
    }

    public function normalize($word) : string
    {
        return Strings::normalize($word);
    }

    /**
     * Creates new word
     * 
     * Word should be normalized in advance!
     * 
     * !!!!!!!!!!!!!!!!!!!
     * Same problem as with duplicate association
     * Two users can add the same word in parallel
     * !!!!!!!!!!!!!!!!!!!
     */
    public function create(Language $language, string $wordStr, User $user) : Word
    {
        if ($language === null) {
            throw new InvalidArgumentException('Language must be non-null.');
        }
        
        if (strlen($wordStr) === 0) {
            throw new InvalidArgumentException('Word can\'t be empty.');
        }

        if ($user === null) {
            throw new InvalidArgumentException('User must be non-null.');
        }
        
        if (Word::findInLanguage($language, $wordStr) !== null) {
            throw new InvalidOperationException('Word already exists.');
        }
        
        $word = Word::create();
        
        $word->languageId = $language->getId();
        $word->word = $wordStr;
        $word->wordBin = $wordStr;
        $word->createdBy = $user->getId();

        return $word->save();
    }

    /**
     * Returns validation rules chain for word
     */
    public function getRule() : Validator
    {
        $rules = new ValidationRules($this->container);

        return $rules
            ->get('text')
            ->length($this->config->wordMinLength(), $this->config->wordMaxLength())
            ->wordIsValid();
    }

    public function validateWord(string $wordStr) : void
    {
        $validation = $this->validator->validateArray(
            ['word' => $wordStr],
            ['word' => $this->getRule()]
        );
        
        if ($validation->failed()) {
            throw new ValidationException($validation->errors);
        }
    }
}
