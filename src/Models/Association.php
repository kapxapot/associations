<?php

namespace App\Models;

use App\Collections\AssociationFeedbackCollection;
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
    protected ?AssociationFeedbackCollection $feedbacks = null;

    private bool $firstWordInitialized = false;
    private bool $secondWordInitialized = false;
    private bool $feedbacksInitialized = false;

    public function words() : WordCollection
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

    public function feedbacks() : AssociationFeedbackCollection
    {
        Assert::true($this->feedbacksInitialized);

        return $this->feedbacks;
    }

    public function withFeedbacks(AssociationFeedbackCollection $feedbacks) : self
    {
        $this->feedbacks = $feedbacks;
        $this->feedbacksInitialized = true;

        return $this;
    }

    public function dislikes() : AssociationFeedbackCollection
    {
        return $this->feedbacks()->dislikes();
    }

    public function matures() : AssociationFeedbackCollection
    {
        return $this->feedbacks()->matures();
    }

    public function feedbackBy(User $user) : ?AssociationFeedback
    {
        return $this->feedbacks()->firstBy($user);
    }

    public function feedbackByMe() : ?AssociationFeedback
    {
        return $this->me()
            ? $this->feedbackBy($this->me())
            : null;
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
}
