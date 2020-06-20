<?php

namespace App\Models;

use App\Collections\AssociationCollection;
use App\Collections\WordCollection;
use App\Collections\WordFeedbackCollection;
use App\Models\Interfaces\DictWordInterface;

/**
 * @property string $word
 * @method AssociationCollection associations()
 * @method DictWordInterface|null dictWord()
 * @method static withAssociations(AssociationCollection|callable $associations)
 * @method static withDictWord(DictWordInterface|callable|null $dictWord)
 * @method static withFeedbacks(WordFeedbackCollection|callable $feedbacks)
 */
class Word extends LanguageElement
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'associations',
        ];
    }

    public function feedbacks() : WordFeedbackCollection
    {
        return WordFeedbackCollection::from(
            parent::feedbacks()
        );
    }

    public function dislikes() : WordFeedbackCollection
    {
        return $this->feedbacks()->dislikes();
    }

    public function matures() : WordFeedbackCollection
    {
        return $this->feedbacks()->matures();
    }

    public function feedbackBy(User $user) : ?WordFeedback
    {
        return $this->feedbacks()->firstBy($user);
    }

    public function feedbackByMe() : ?WordFeedback
    {
        return $this->me()
            ? $this->feedbackBy($this->me())
            : null;
    }

    public function approvedAssociations() : AssociationCollection
    {
        return $this
            ->associations()
            ->approved()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    /**
     * Returns approved associations that are visible for everyone (public).
     */
    public function publicAssociations() : AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->public();
    }

    public function approvedVisibleAssociations() : AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->visibleFor($this->me());
    }

    public function approvedInvisibleAssociations() : AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->invisibleFor($this->me());
    }

    public function notApprovedAssociations() : AssociationCollection
    {
        return $this
            ->associations()
            ->notApproved()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    public function notApprovedVisibleAssociations() : AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->visibleFor($this->me());
    }

    public function notApprovedInvisibleAssociations() : AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->invisibleFor($this->me());
    }

    public function associatedWordsFor(User $user) : WordCollection
    {
        return WordCollection::from(
            $this
                ->associations()
                ->playableAgainst($user)
                ->map(
                    fn (Association $a) => $a->otherWord($this)
                )
        );
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

    public function proposedTypos() : array
    {
        return $this
            ->feedbacks()
            ->typos()
            ->group(
                fn (WordFeedback $f) => $f->typo
            );
    }

    public function proposedDuplicates() : array
    {
        return $this
            ->feedbacks()
            ->duplicates()
            ->group(
                fn (WordFeedback $f) => $f->duplicateId
            );
    }

    /**
     * Returns the typo provided by the current user.
     */
    public function typoByMe() : ?string
    {
        $feedback = $this->feedbackByMe();

        return ($feedback && strlen($feedback->typo) > 0)
            ? $feedback->typo
            : null;
    }

    /**
     * Returns word or typo by the current user with '*' (if any).
     */
    public function displayName() : string
    {
        $typo = $this->typoByMe();

        return is_null($typo)
            ? $this->word
            : $typo . '*';
    }

    /**
     * Returns the original word + typo by the current user.
     */
    public function fullDisplayName() : string
    {
        $name = $this->displayName();

        if ($this->typoByMe() !== null) {
            $name .= ' (' . $this->word . ')';
        }

        return $name;
    }
}
