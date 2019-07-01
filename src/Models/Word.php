<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

class Word extends DbModel
{
    use Created;
    
    protected static $sortField = 'word';
    
    // queries
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::baseQuery()
            ->where('language_id', $language->getId());
    }
    
    public static function getCreatedByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return $query->where('created_by', $user->getId());
    }
    
    public static function getApproved(Language $language = null) : Query
    {
        return self::staticLazy(function () use ($language) {
            $query = ($language !== null)
                ? self::getByLanguage($language)
                : self::query();
            
            return $query->where('approved', 1);
        });
    }

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
    
    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public function associations() : Query
    {
        return Association::getByWord($this);
    }
    
    public function approvedAssociations() : Query
    {
        return Association::filterApproved($this->associations());
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
    
    public function turnsByUsers()
    {
        return $this
            ->turns()
            ->whereNotNull('user_id')
            ->all()
            ->group('user_id');
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
    
    public function feedbackByUser(User $user) : ?WordFeedback
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function currentFeedback() : ?WordFeedback
    {
        $user = self::getCurrentUser();
        
        return $user !== null
            ? $this->feedbackByUser($user)
            : null;
    }
    
    public function proposedTypos()
    {
        return $this->feedbacks()
            ->whereNotNull('typo')
            ->all()
            ->group('typo');
    }
    
    public function proposedDuplicates()
    {
        return $this->feedbacks()
            ->whereNotNull('duplicate_id')
            ->all()
            ->group('duplicate_id');
    }
    
    public function dislikes() : Query
    {
        return WordFeedback::filterDisliked($this->feedbacks());
    }
    
    public function matures() : Query
    {
        return WordFeedback::filterMature($this->feedbacks());
    }
    
    public function isApproved() : bool
    {
        return $this->approved === 1;
    }

    public function isMature() : bool
    {
        return $this->mature === 1;
    }
    
    public function isUsedByUser(User $user) : bool
    {
        return $this
            ->turns()
            ->where('user_id', $user->getId())
            ->any();
    }

    public function isDislikedByUser(User $user) : bool
    {
        return
            WordFeedback::filterByCreator(
                $this->dislikes(),
                $user
            )
            ->any();
    }
    
    /**
     * Maturity check.
     */
    public function isVisibleForUser(User $user = null)
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

    public function isPlayableAgainstUser(User $user)
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
