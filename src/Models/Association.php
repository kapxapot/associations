<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;

class Association extends Element
{
    // queries
    
    public static function getByWord(Word $word) : Query
    {
        return self::baseQuery()
            ->whereAnyIs([
                ['first_word_id' => $word->getId()],
                ['second_word_id' => $word->getId()],
            ]);
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
     * Returns one of the association's word different from the provided one.
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
    
    public function feedbacks() : Query
    {
        return AssociationFeedback::getByAssociation($this);
    }
    
    public function feedbackByUser(User $user) : ?Feedback
    {
        return AssociationFeedback::getByAssociationAndUser($this, $user);
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
