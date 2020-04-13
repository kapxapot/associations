<?php

namespace App\Models;

use App\Collections\AssociationFeedbackCollection;
use App\Collections\UserCollection;
use App\Collections\WordCollection;

/**
 * @property int $firstWordId
 * @property int $secondWordId
 * @method Word firstWord()
 * @method Word secondWord()
 * @method string url()
 * @method self withFeedbacks(AssociationFeedbackCollection|callable $feedbacks)
 * @method self withFirstWord(Word|callable $firstWord)
 * @method self withSecondWord(Word|callable $secondWord)
 * @method self withUrl(string|callable $url)
 */
class Association extends LanguageElement
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'feedbacks',
            'firstWord',
            'secondWord',
            'url'
        ];
    }

    public function words() : WordCollection
    {
        return WordCollection::make(
            [
                $this->firstWord(),
                $this->secondWord()
            ]
        );
    }

    public function feedbacks() : AssociationFeedbackCollection
    {
        return $this->getWithProperty('feedbacks');
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
