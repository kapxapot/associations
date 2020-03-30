<?php

namespace App\Models;

use App\Collections\UserCollection;
use App\Collections\WordCollection;
use Plasticode\Models\Traits\WithUrl;
use Webmozart\Assert\Assert;

/**
 * @property int $firstWordId
 * @property int $secondWordId
 */
class Association extends LanguageElement
{
    use WithUrl;

    protected ?Word $firstWord = null;
    protected ?Word $secondWord = null;

    private bool $firstWordInitialized = false;
    private bool $secondWordInitialized = false;

    public function words(): WordCollection
    {
        return WordCollection::make(
            [
                $this->firstWord(),
                $this->secondWord()
            ]
        );
    }

    public function firstWord() : Word
    {
        Assert::true($this->firstWordInitialized);

        return $this->firstWord;
    }

    public function withFirstWord(Word $firstWord) : self
    {
        $this->firstWord = $firstWord;
        $this->firstWordInitialized = true;

        return $this;
    }

    public function secondWord() : Word
    {
        Assert::true($this->secondWordInitialized);

        return $this->secondWord;
    }

    public function withSecondWord(Word $secondWord) : self
    {
        $this->secondWord = $secondWord;
        $this->secondWordInitialized = true;

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

    /**
     * Users that used this association.
     */
    public function users() : UserCollection
    {
        return $this->turns()->users();
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
