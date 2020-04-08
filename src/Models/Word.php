<?php

namespace App\Models;

use App\Collections\AssociationCollection;
use App\Collections\WordCollection;
use App\Collections\WordFeedbackCollection;
use Plasticode\Collection;
use Plasticode\Models\Traits\WithUrl;
use Webmozart\Assert\Assert;

/**
 * @property string $word
 */
class Word extends LanguageElement
{
    use WithUrl;

    protected ?AssociationCollection $associations = null;
    protected ?WordFeedbackCollection $feedbacks = null;

    private bool $associationsInitialized = false;
    private bool $feedbacksInitialized = false;

    public function associations() : AssociationCollection
    {
        Assert::true($this->associationsInitialized);

        return $this->associations;
    }

    public function withAssociations(AssociationCollection $associations) : self
    {
        $this->associations = $associations;
        $this->associationsInitialized = true;

        return $this;
    }

    public function feedbacks() : WordFeedbackCollection
    {
        Assert::true($this->feedbacksInitialized);

        return $this->feedbacks;
    }

    public function withFeedbacks(WordFeedbackCollection $feedbacks) : self
    {
        $this->feedbacks = $feedbacks;
        $this->feedbacksInitialized = true;

        return $this;
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

    private function compareByOtherWord() : \Closure
    {
        return fn (Association $assocA, Association $assocB) : int =>
            strcmp(
                $assocA->otherWord($this)->word,
                $assocB->otherWord($this)->word
            );
    }
    
    public function approvedAssociations() : AssociationCollection
    {
        return $this
            ->associations()
            ->approved()
            ->orderByFunc($this->compareByOtherWord());
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
            ->orderByFunc($this->compareByOtherWord());
    }

    public function notApprovedVisibleAssociations() : AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->visibleFor($this->me());
    }

    public function notApprovedInvisibleAssociations() : Collection
    {
        return $this
            ->notApprovedAssociations()
            ->invisibleFor($this->me());
    }

    public function associatedWordsFor(User $user) : WordCollection
    {
        return $this
            ->associations()
            ->playableAgainst($user)
            ->map(
                fn (Association $a) => $a->otherWord($this)
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

        return (!is_null($feedback) && strlen($feedback->typo) > 0)
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
