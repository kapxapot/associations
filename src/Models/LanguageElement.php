<?php

namespace App\Models;

use App\Collections\FeedbackCollection;
use App\Collections\TurnCollection;
use App\Models\Traits\WithLanguage;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\UpdatedAt;
use Webmozart\Assert\Assert;

/**
 * @property integer $approved
 * @property string|null $approvedUpdatedAt
 * @property integer $mature
 * @property string|null $matureUpdatedAt
 */
abstract class LanguageElement extends DbModel
{
    use Created, UpdatedAt, WithLanguage;

    protected ?TurnCollection $turns = null;
    protected ?FeedbackCollection $feedbacks = null;

    /**
     * Current user
     */
    protected ?User $me = null;

    private bool $turnsInitialized = false;
    private bool $feedbacksInitialized = false;
    private bool $meInitialized = false;

    public function turns() : TurnCollection
    {
        Assert::true($this->turnsInitialized);

        return $this->turns;
    }

    public function withTurns(TurnCollection $turns) : self
    {
        $this->turns = $turns;
        $this->turnsInitialized = true;

        return $this;
    }

    public function feedbacks() : FeedbackCollection
    {
        Assert::true($this->feedbacksInitialized);

        return $this->feedbacks;
    }

    public function withFeedbacks(FeedbackCollection $feedbacks) : self
    {
        $this->feedbacks = $feedbacks;
        $this->feedbacksInitialized = true;

        return $this;
    }

    protected function me() : User
    {
        Assert::true($this->meInitialized);

        return $this->me;
    }

    public function withMe(User $me) : self
    {
        $this->me = $me;
        $this->meInitialized = true;

        return $this;
    }

    public function dislikes() : FeedbackCollection
    {
        return $this->feedbacks()->dislikes();
    }

    public function matures() : FeedbackCollection
    {
        return $this->feedbacks()->matures();
    }

    public function isDislikedBy(User $user) : bool
    {
        return $this->dislikes()->anyBy($user);
    }

    public function isUsedBy(User $user) : bool
    {
        return $this->turns()->anyBy($user);
    }

    public abstract function isVisibleFor(User $user = null) : bool;

    public abstract function isPlayableAgainst(User $user) : bool;

    public function isVisibleForMe() : bool
    {
        return $this->isVisibleFor($this->me());
    }

    public function isPlayableAgainstMe() : bool
    {
        return $this->isPlayableAgainst($this->me());
    }

    public function feedbackBy(User $user) : ?Feedback
    {
        return $this->feedbacks()->firstBy($user);
    }

    public function feedbackByMe() : ?Feedback
    {
        return $this->feedbackBy($this->me());
    }

    public function isApproved() : bool
    {
        return self::toBool($this->approved);
    }

    public function isMature() : bool
    {
        return self::toBool($this->mature);
    }

    public function approvedUpdatedAtIso() : ?string
    {
        return self::toIso($this->approvedUpdatedAt);
    }

    public function matureUpdatedAtIso() : ?string
    {
        return self::toIso($this->matureUpdatedAt);
    }
}
