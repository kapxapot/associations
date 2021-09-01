<?php

namespace App\Models;

use App\Collections\FeedbackCollection;
use App\Collections\OverrideCollection;
use App\Collections\TurnCollection;
use App\Models\DTO\GameOptions;
use App\Models\Traits\Created;
use App\Semantics\Scope;
use App\Semantics\Severity;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $disabled Deprecated.
 * @property integer $languageId
 * @property integer $mature Deprecated.
 * @property integer $scope
 * @property string|null $scopeUpdatedAt
 * @property integer $severity
 * @property string|null $severityUpdatedAt
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

    public function isDislikedBy(?User $user): bool
    {
        return $user !== null
            ? $this->dislikes()->anyBy($user)
            : false;
    }

    abstract public function dislikes(): FeedbackCollection;

    abstract public function matures(): FeedbackCollection;

    public function isUsedBy(?User $user): bool
    {
        return $user !== null
            ? $this->turns()->anyBy($user)
            : false;
    }

    /**
     * Is visible for everyone.
     */
    public function isVisible(): bool
    {
        return $this->isVisibleFor(null);
    }

    public function isVisibleFor(?User $user): bool
    {
        // 1. for disabled:
        // 1.1. visible only for those who used them
        // 2. for enabled:
        // 2.1. non-mature elements are visible for everyone
        // 2.2. mature elements are invisible for non-authed users ($user == null)
        // 2.3. mature elements are visible for mature users
        // 2.4. mature elements are visible for non-mature users only if they used them

        if ($this->isDisabled()) {
            return $this->isUsedBy($user);
        }

        if (!$this->isMature()) {
            return true;
        }

        return $user && $user->isMature() || $this->isUsedBy($user);
    }

    public function isPlayableAgainstAll(?GameOptions $options = null): bool
    {
        return $this->isPlayableAgainst(null, $options);
    }

    public function isPlayableAgainst(?User $user, ?GameOptions $options = null): bool
    {
        $allowPrivate = $options && $options->allowPrivateElements;

        return $this->isVisibleFor($user)
            && ($this->isPublic() || $allowPrivate || $this->isUsedBy($user))
            && !$this->isDislikedBy($user);
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

    /**
     * @deprecated Use `isPublic()`.
     */
    public function isApproved(): bool
    {
        return $this->isPublic();
    }

    public function isPublic(): bool
    {
        return Scope::isPublic($this->scope);
    }

    public function isMature(): bool
    {
        return Severity::isMature($this->severity);
    }

    public function isDisabled(): bool
    {
        return Scope::isDisabled($this->scope);
    }

    public function scopeUpdatedAtIso(): ?string
    {
        return self::toIso($this->scopeUpdatedAt);
    }

    public function severityUpdatedAtIso(): ?string
    {
        return self::toIso($this->severityUpdatedAt);
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
