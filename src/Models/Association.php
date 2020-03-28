<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;

/**
 * @property int $firstWordId
 * @property int $secondWordId
 */
class Association extends LanguageElement
{
    private ?Word $firstWord = null;
    private ?Word $secondWord = null;

    public function words() : Collection
    {
        return Collection::make(
            [
                $this->firstWord,
                $this->secondWord
            ]
        );
    }

    public function firstWord() : Word
    {
        return $this->firstWord;
    }

    public function withFirstWord(Word $firstWord) : self
    {
        $this->firstWord = $firstWord;
        return $this;
    }

    public function secondWord() : Word
    {
        return $this->secondWord;
    }

    public function withSecondWord(Word $secondWord) : self
    {
        $this->secondWord = $secondWord;
        return $this;
    }

    /**
     * Returns one of the association's words different from the provided one.
     */
    public function otherWord(Word $word) : Word
    {
        return $this->firstWord()->getId() === $word->getId()
            ? $this->secondWord()
            : $this->firstWord();
    }
    
    public function url() : ?string
    {
        return self::$container->linker->association($this);
    }
    
    /**
     * Turns with this association.
     */
    public function turns() : Query
    {
        return Turn::getByAssociation($this);
    }

    /**
     * Users that used this association.
     */
    public function users() : Collection
    {
        $userIds = array_keys($this->turnsByUsers());

        return Collection::make($userIds)
            ->map(
                fn ($id) => self::$container->userRepository->get($id)
            );
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
