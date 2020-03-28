<?php

namespace App\Models;

use Plasticode\Collection;

/**
 * @property int $firstWordId
 * @property int $secondWordId
 */
class Association extends LanguageElement
{
    private ?Word $firstWord = null;
    private ?Word $secondWord = null;
    private ?string $url = null;

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
        return $this->url;
    }

    public function withUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }
    
    /**
     * Turns with this association.
     */
    public function turns() : Collection
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
    
    public function feedbacks() : Collection
    {
        return AssociationFeedback::getByAssociation($this);
    }
    
    public function feedbackBy(User $user) : ?Feedback
    {
        return AssociationFeedback::getByAssociationAndUser($this, $user);
    }

    /**
     * Maturity check.
     */
    public function isVisibleFor(User $user = null) : bool
    {
        // 1. non-mature words are visible for everyone
        // 2. mature words are invisible for non-authed users ($user == null)
        // 3. mature words are visible for non-mature users only if they used the word

        return 
            !$this->isMature() ||
            ($user !== null &&
                ($user->isMature() || $this->isUsedBy($user))
            );
    }

    public function isPlayableAgainst(User $user) : bool
    {
        // word can't be played against user, if
        //
        // 1. word is mature, user is not mature (maturity check)
        // 2. word is not approved, user disliked the word

        return $this->isVisibleFor($user) &&
            ($this->isApproved() ||
                ($this->isUsedBy($user) && !$this->isDislikedBy($user))
            );
    }
}
