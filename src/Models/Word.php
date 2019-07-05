<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;

class Word extends Element
{
    protected static $sortField = 'word';

    // getters - one
    
    /**
     * Finds the word by string in the specified language.
     * 
     * Normalized word string expected.
     */
    public static function findInLanguage(Language $language, string $wordStr) : ?Word
    {
        return self::getByLanguage($language)
            ->where('word_bin', $wordStr)
            ->one();
    }
    
    // properties
    
    public function associations() : Query
    {
        return Association::getByWord($this);
    }
    
    public function approvedAssociations() : Collection
    {
        return Association::filterApproved($this->associations())
            ->all();
    }
    
    public function unapprovedAssociations() : Collection
    {
        return Association::filterUnapproved($this->associations())
            ->all();
    }
    
    public function associationsForUser(User $user) : Collection
    {
        return $this->lazy(function () use ($user) {
            return $this->associations()
                ->all()
                ->where(function ($assoc) use ($user) {
                    return $assoc->isPlayableAgainstUser($user);
                });
        });
    }

    public function associatedWords(User $user) : Collection
    {
        return $this->associationsForUser($user)
            ->map(function ($assoc) {
                return $assoc->otherWord($this);
            });
    }
    
    public function url() : ?string
    {
        return self::$linker->word($this);
    }
    
    public function turns() : Query
    {
        return Turn::getByWord($this);
    }
    
    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'word' => $this->word,
            'url' => $this->url(),
            'language' => $this->language()->serialize(),
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
    
    public function feedbacks() : Query
    {
        return WordFeedback::getByWord($this);
    }
    
    public function feedbackByUser(User $user) : ?Feedback
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function proposedTypos() : array
    {
        return $this->feedbacks()
            ->whereNotNull('typo')
            ->all()
            ->group('typo');
    }
    
    public function proposedDuplicates() : array
    {
        return $this->feedbacks()
            ->whereNotNull('duplicate_id')
            ->all()
            ->group('duplicate_id');
    }
    
    /**
     * Maturity check.
     */
    public function isVisibleForUser(User $user = null) : bool
    {
        // 1. non-mature words are visible for everyone
        // 2. mature words are invisible for non-authed users ($user == null)
        // 3. mature words are visible for non-mature users only if they used the word

        return 
            !$this->isMature() ||
            ($user !== null &&
                ($user->isMature() || $this->isUsedByUser($user))
            );
    }

    public function isPlayableAgainstUser(User $user) : bool
    {
        // word can't be played against user, if
        //
        // 1. word is mature, user is not mature (maturity check)
        // 2. word is not approved, user disliked the word
        
        return $this->isVisibleForUser($user) &&
            ($this->isApproved() ||
                ($this->isUsedByUser($user) && !$this->isDislikedByUser($user))
            );
    }
}
