<?php

namespace App\Models;

use App\Collections\FeedbackCollection;
use App\Collections\TurnCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $approved
 * @property string|null $approvedUpdatedAt
 * @property integer $mature
 * @property string|null $matureUpdatedAt
 * @method Language language()
 * @method User|null me()
 * @method TurnCollection turns()
 * @method static withLanguage(Language|callable $language)
 * @method static withMe(User|callable|null $me)
 * @method static withTurns(TurnCollection|callable $turns)
 */
abstract class LanguageElement extends DbModel
{
    use Created;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['creator', 'language', 'me', 'turns'];
    }

    abstract public function feedbacks() : FeedbackCollection;

    abstract public function dislikes() : FeedbackCollection;

    abstract public function matures() : FeedbackCollection;

    public function isDislikedBy(User $user) : bool
    {
        return $this->dislikes()->anyBy($user);
    }

    public function isUsedBy(User $user) : bool
    {
        return $this->turns()->anyBy($user);
    }

    /**
     * Maturity check.
     */
    public function isVisibleFor(?User $user) : bool
    {
        // 1. non-mature elements are visible for everyone
        // 2. mature elements are invisible for non-authed users ($user == null)
        // 3. mature elements are visible for non-mature users only if they used it

        return 
            !$this->isMature()
            || (
                $user
                && ($user->isMature() || $this->isUsedBy($user))
            );
    }

    public function isPlayableAgainst(User $user) : bool
    {
        // element can't be played against user, if
        //
        // 1. element is mature, user is not mature (maturity check)
        // 2. eleemnt is not approved, user disliked it

        return $this->isVisibleFor($user)
            && (
                $this->isApproved()
                || ($this->isUsedBy($user) && !$this->isDislikedBy($user))
            );
    }

    public function isVisibleForMe() : bool
    {
        return $this->isVisibleFor($this->me());
    }

    public function isPlayableAgainstMe() : bool
    {
        return $this->me()
            ? $this->isPlayableAgainst($this->me())
            : false;
    }

    abstract public function feedbackBy(User $user) : ?Feedback;

    abstract public function feedbackByMe() : ?Feedback;

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
