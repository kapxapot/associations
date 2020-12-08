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
 * @method static withFeedbacks(AssociationFeedbackCollection|callable $feedbacks)
 * @method static withFirstWord(Word|callable $firstWord)
 * @method static withSecondWord(Word|callable $secondWord)
 */
class Association extends LanguageElement
{
    const DEFAULT_SIGN = '→';
    const APPROVED_SIGN = '⇉';

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            'firstWord',
            'secondWord',
        ];
    }

    public function words() : WordCollection
    {
        return WordCollection::collect(
            $this->firstWord(),
            $this->secondWord()
        );
    }

    public function hasWords(Word $first, Word $second) : bool
    {
        // the same word is NO
        if ($first->equals($second)) {
            return false;
        }

        $ids = $this->words()->ids();

        return $ids->contains($first->getId()) && $ids->contains($second->getId());
    }

    public function feedbacks() : AssociationFeedbackCollection
    {
        return AssociationFeedbackCollection::from(
            parent::feedbacks()
        );
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
        return $this->firstWord()->equals($word)
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
     * Users + creator (in case when turns are deleted/lost).
     */
    public function extendedUsers() : UserCollection
    {
        return $this
            ->users()
            ->add($this->creator())
            ->distinct();
    }

    public function hasMatureWords() : bool
    {
        return $this
            ->words()
            ->any(
                fn (Word $w) => $w->isMature()
            );
    }

    public function sign() : string
    {
        return $this->isApproved()
            ? self::APPROVED_SIGN
            : self::DEFAULT_SIGN;
    }
}
