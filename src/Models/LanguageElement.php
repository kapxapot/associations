<?php

namespace App\Models;

use App\Collections\FeedbackCollection;
use App\Collections\OverrideCollection;
use App\Collections\TurnCollection;
use App\Models\Traits\Created;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $approved
 * @property string|null $approvedUpdatedAt
 * @property integer $disabled
 * @property string|null $disabledUpdatedAt
 * @property integer $languageId
 * @property integer $mature
 * @property string|null $matureUpdatedAt
 * @method Language language()
 * @method User|null me()
 * @method TurnCollection turns()
 * @method static withLanguage(Language|callable $language)
 * @method static withMe(User|callable|null $me)
 * @method static withTurns(TurnCollection|callable $turns)
 */
abstract class LanguageElement extends DbModel implements CreatedInterface, LinkableInterface, UpdatedAtInterface
{
    use Created;
    use Linkable;
    use UpdatedAt;

    protected string $feedbacksPropertyName = 'feedbacks';
    protected string $overridesPropertyName = 'overrides';

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
            $this->feedbacksPropertyName,
            $this->overridesPropertyName,
            $this->urlPropertyName,
            'language',
            'me',
            'turns',
        ];
    }

    public function feedbacks(): FeedbackCollection
    {
        return $this->getWithProperty(
            $this->feedbacksPropertyName
        );
    }

    public function overrides(): OverrideCollection
    {
        return $this->getWithProperty(
            $this->overridesPropertyName
        );
    }

    abstract public function dislikes(): FeedbackCollection;

    abstract public function matures(): FeedbackCollection;

    public function isDislikedBy(User $user): bool
    {
        return $this->dislikes()->anyBy($user);
    }

    public function isUsedBy(User $user): bool
    {
        return $this->turns()->anyBy($user);
    }

    /**
     * Is visible for everyone.
     *
     * Equivalent to "not disabled" & "non mature".
     */
    public function isVisible(): bool
    {
        return $this->isVisibleFor(null);
    }

    public function isVisibleFor(?User $user): bool
    {
        // 1. for enabled:
        // 1.1. non-mature elements are visible for everyone
        // 1.2. mature elements are invisible for non-authed users ($user == null)
        // 1.3. mature elements are visible for mature users
        // 1.4. mature elements are visible for non-mature users only if they used them
        // 2. for disabled:
        // 2.1. visible only for those who used them

        if ($this->isDisabled()) {
            return $user !== null && $this->isUsedBy($user);
        }

        return
            !$this->isMature()
            || (
                $user
                && ($user->isMature() || $this->isUsedBy($user))
            );
    }

    /**
     * Is visible for all (public) and is approved.
     */
    public function isPlayableAgainstAll(): bool
    {
        return $this->isPlayableAgainst(null);
    }

    public function isPlayableAgainst(?User $user): bool
    {
        // element can't be played against user, if
        //
        // 1. element is mature, user is not mature (maturity check)
        // 2. element is not approved, user disliked it

        return $this->isVisibleFor($user)
            && (
                $this->isApproved()
                || ($user && $this->isUsedBy($user) && !$this->isDislikedBy($user))
            );
    }

    public function isVisibleForMe(): bool
    {
        return $this->isVisibleFor($this->me());
    }

    public function isPlayableAgainstMe(): bool
    {
        return $this->me()
            ? $this->isPlayableAgainst($this->me())
            : false;
    }

    abstract public function feedbackBy(User $user): ?Feedback;

    abstract public function feedbackByMe(): ?Feedback;

    public function isApproved(): bool
    {
        return self::toBool($this->approved);
    }

    public function isMature(): bool
    {
        return self::toBool($this->mature);
    }

    public function approvedUpdatedAtIso(): ?string
    {
        return self::toIso($this->approvedUpdatedAt);
    }

    public function matureUpdatedAtIso(): ?string
    {
        return self::toIso($this->matureUpdatedAt);
    }

    public function isDisabled(): bool
    {
        return self::toBool($this->disabled);
    }

    public function disabledUpdatedAtIso(): ?string
    {
        return self::toIso($this->disabledUpdatedAt);
    }

    abstract public function override(): ?Override;

    public function hasOverride(): bool
    {
        return $this->override() !== null;
    }

    /**
     * Returns true if the element has an override AND
     * that override has some actual changes (is not empty).
     */
    public function hasActualOverride(): bool
    {
        return $this->hasOverride() && $this->override()->isNotEmpty();
    }

    public function hasApprovedOverride(): bool
    {
        return $this->approvedOverride() !== null;
    }

    public function approvedOverride(): ?bool
    {
        return $this->hasOverride()
            ? $this->override()->isApproved()
            : null;
    }

    public function hasMatureOverride(): bool
    {
        return $this->matureOverride() !== null;
    }

    public function matureOverride(): ?bool
    {
        return $this->hasOverride()
            ? $this->override()->isMature()
            : null;
    }

    public function hasDisabledOverride(): bool
    {
        return $this->disabledOverride() === true;
    }

    public function disabledOverride(): ?bool
    {
        return $this->hasOverride()
            ? $this->override()->isDisabled()
            : null;
    }
}
