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
    
    public static function findInLanguage(Language $language, string $word)
    {
        return self::getByLanguage($language)
            ->where('word_bin', $word)
            ->one();
    }
    
    public static function getCreatedByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();

        return $query->where('created_by', $user->getId());
    }

    public static function getUsedByUser(User $user, Language $language = null) : Collection
    {
        return $user->wordsUsed($language);
    }

    // getters - many
    
    public static function getApproved(Language $language = null, bool $excludeMature = null) : Collection
    {
        return self::staticLazy(function () use ($language, $excludeMature) {
            $query = ($language !== null)
                ? self::getByLanguage($language)
                : self::query();
            
            return $query
                ->all()
                ->where(function ($word) use ($excludeMature) {
                    return $word->isApproved() && ($excludeMature !== true || !$word->isMature());
                });
        });
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
    
    public function approvedAssociations() : Collection
    {
        return $this->lazy(function () {
            return $this->associations()
                ->all()
                ->where(function ($assoc) {
                    return $assoc->isApproved();
                });
        });
    }
    
    public function unapprovedAssociations() : Collection
    {
        return $this->lazy(function () {
            return $this->associations()
                ->all()
                ->where(function ($assoc) {
                    return !$assoc->isApproved();
                });
        });
    }
    
    public function associationsForUser(User $user) : Collection
    {
        return $this->lazy(function () use ($user) {
            return $this->associations()
                ->all()
                ->where(function ($assoc) use ($user) {
                    return ($assoc->isApproved() && ($user->isMature() || !$assoc->isMature())) || $user->getId() === $assoc->creator()->getId();
                });
        });
    }
    
    public function score()
    {
        return $this->lazy(function () {
            $approvedAssocs = $this->approvedAssociations();
            $approvedAssocsCount = count($approvedAssocs);
            
            $dislikeCount = $this->dislikes()->count();
            
            $assocCoeff = self::getSettings('words.coeffs.approved_association');
            $dislikeCoeff = self::getSettings('words.coeffs.dislike');
            
            return $approvedAssocsCount * $assocCoeff - $dislikeCount * $dislikeCoeff;
        });
    }
    
    public function isApproved() : bool
    {
        return $this->lazy(function () {
            $threshold = self::getSettings('words.approval_threshold');
        
            return $this->score() >= $threshold;
        });
    }

    public function associatedWords(User $user) : Collection
    {
        return $this->associationsForUser($user)
            ->map(function ($assoc) {
                return $assoc->firstWord->getId() === $this->getId()
                    ? $assoc->secondWord()
                    : $assoc->firstWord();
            });
    }
    
    public function url()
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
    
    /**
     * Currently not used.
     */
    public function isApprovedByUsage() : bool
    {
        $threshold = self::getSettings('words.approval_threshold');
        $turnsByUsers = $this->turnsByUsers();
        
        return count($turnsByUsers) >= $threshold;
    }
        
    public function serialize()
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
    
    public function feedbackByUser(User $user)
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function currentFeedback()
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
        return $this->feedbacks()
            ->where('dislike', 1);
    }
    
    public function matures() : Query
    {
        return $this->feedbacks()
            ->where('mature', 1);
    }

    public function isMature() : bool
    {
        return $this->lazy(function () {
            $threshold = self::getSettings('words.mature_threshold');
        
            return $this->matures()->count() >= $threshold;
        });
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
        return $this
            ->dislikes()
            ->where('created_by', $user->getId())
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
