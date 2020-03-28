<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;

/**
 * @property integer $languageId
 * @property integer $approved
 * @property string|null $approvedUpdatedAt
 * @property integer $mature
 * @property string|null $matureUpdatedAt
 */
abstract class LanguageElement extends DbModel
{
    use Created;

    private Language $language;
    private Collection $turns;
    private Collection $feedbacks;

    /**
     * Current user
     */
    private ?User $me = null;

    public function language() : Language
    {
        return $this->language;
    }

    public function withLanguage(Language $language) : self
    {
        $this->language = $language;
        return $this;
    }

    public function turns() : Collection
    {
        return $this->turns;
    }

    public function withTurns(Collection $turns) : self
    {
        $this->turns = $turns;
        return $this;
    }

    public function feedbacks() : Collection
    {
        return $this->feedbacks;
    }

    public function withFeedbacks(Collection $feedbacks) : self
    {
        $this->feedbacks = $feedbacks;
        return $this;
    }

    public function withMe(User $me) : self
    {
        $this->me = $me;
        return $this;
    }

    public function dislikes() : Collection
    {
        return $this
            ->feedbacks()
            ->where(
                fn (Feedback $f) => $f->isDisliked()
            );
    }
    
    public function matures() : Collection
    {
        return $this
            ->feedbacks()
            ->where(
                fn (Feedback $f) => $f->isMature()
            );
    }

    public function isDislikedBy(User $user) : bool
    {
        return $this
            ->dislikes()
            ->where(
                fn (Feedback $f) => $f->isCreatedBy($user)
            )
            ->any();
    }

    public function isUsedBy(User $user) : bool
    {
        return $this
            ->turns()
            ->where(
                fn (Turn $t) => $t->isBy($user)
            )
            ->any();
    }

    public abstract function isVisibleFor(User $user = null) : bool;

    public abstract function isPlayableAgainst(User $user) : bool;

    public function isVisibleForMe() : bool
    {
        return $this->isVisibleFor($this->me);
    }

    public function isPlayableAgainstMe() : bool
    {
        return $this->isPlayableAgainst($this->me);
    }

    public abstract function feedbackBy(User $user) : ?Feedback;

    public function currentFeedback() : ?Feedback
    {
        return $this->me
            ? $this->feedbackBy($this->me)
            : null;
    }

    public function isApproved() : bool
    {
        return Convert::fromBit($this->approved);
    }

    public function isMature() : bool
    {
        return Convert::fromBit($this->mature);
    }

    public function updatedAtIso() : string
    {
        return Date::iso($this->updatedAt);
    }

    public function approvedUpdatedAtIso() : ?string
    {
        return $this->approvedUpdatedAt
            ? Date::iso($this->approvedUpdatedAt)
            : null;
    }

    public function matureUpdatedAtIso() : ?string
    {
        return $this->matureUpdatedAt
            ? Date::iso($this->matureUpdatedAt)
            : null;
    }

    // filtered collections

    protected function filterVisibleForMe(Collection $elements) : Collection
    {
        return $elements
            ->where(
                fn (self $el) => $el->isVisibleForMe()
            );
    }

    protected function filterInvisibleForMe(Collection $elements) : Collection
    {
        return $elements
            ->where(
                fn (self $el) => !$el->isVisibleForMe()
            );
    }
}
