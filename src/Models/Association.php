<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

class Association extends DbModel
{
    use Created;
    
    // queries
    
    public static function getByWord(Word $word) : Query
    {
        return self::baseQuery()
            ->whereAnyIs([
                [ 'first_word_id' => $word->getId() ],
                [ 'second_word_id' => $word->getId() ],
            ]);
    }
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::baseQuery()
            ->where('language_id', $language->getId());
    }

    public static function filterApproved(Query $query) : Query
    {
        return $query->where('approved', 1);
    }

    public static function filterMature(Query $query) : Query
    {
        return $query->where('mature', 1);
    }
    
    public static function getApproved(Language $language = null) : Query
    {
        return self::staticLazy(function () use ($language) {
            $query = ($language !== null)
                ? self::getByLanguage($language)
                : self::query();
            
            return self::filterApproved($query);
        });
    }
    
    // getters - one
    
    public static function getByPair(Word $first, Word $second) : ?self
    {
        return self::baseQuery()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }

    // properties

    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public function words() : Collection
    {
        return Collection::make([$this->firstWord(), $this->secondWord()]);
    }

    public function firstWord()
    {
        return Word::get($this->firstWordId);
    }
    
    public function secondWord()
    {
        return Word::get($this->secondWordId);
    }

    /**
     * Returns on of the association's word different from provided word.
     */
    public function otherWord(Word $word) : Word
    {
        return $this->firstWord()->getId() === $word->getId()
            ? $this->secondWord()
            : $this->firstWord();
    }
    
    public function url() : ?string
    {
        return self::$linker->association($this);
    }
    
    public function turns() : Query
    {
        return Turn::getByAssociation($this);
    }
    
    public function turnsByUsers()
    {
        return $this
            ->turns()
            ->whereNotNull('user_id')
            ->all()
            ->group('user_id');
    }
    
    public function score()
    {
        return $this->lazy(function () {
            $turnsByUsers = $this->turnsByUsers();
            $turnCount = count($turnsByUsers);
            
            $dislikeCount = $this->dislikes()->count();
            
            $usageCoeff = self::getSettings('associations.coeffs.usage');
            $dislikeCoeff = self::getSettings('associations.coeffs.dislike');
            
            return $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;
        });
    }
    
    public function isApproved() : bool
    {
        return $this->lazy(function () {
            $threshold = self::getSettings('associations.approval_threshold');
        
            return $this->score() >= $threshold;
        });
    }
    
    public function feedbacks() : Query
    {
        return AssociationFeedback::getByAssociation($this);
    }
    
    public function feedbackByUser(User $user)
    {
        return AssociationFeedback::getByAssociationAndUser($this, $user);
    }
    
    public function currentFeedback()
    {
        $user = self::getCurrentUser();
        
        return $user !== null
            ? $this->feedbackByUser($user)
            : null;
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
            if ($this->firstWord()->isMature() || $this->secondWord()->isMature()) {
                return true;
            }
    
            $threshold = self::getSettings('associations.mature_threshold');
            
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
