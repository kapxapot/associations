<?php

namespace App\Models;

use App\Collections\FeedbackCollection;
use App\Collections\OverrideCollection;
use App\Collections\TurnCollection;
use App\Models\DTO\GameOptions;
use App\Models\Traits\Created;
use App\Models\Traits\Meta;
use App\Semantics\Scope;
use App\Semantics\Severity;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $languageId
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
    use Meta;
    use UpdatedAt;

    const META_HAS_ACTUAL_OVERRIDE = 'has_actual_override';

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
        // 2.1. not mature elements are visible for everyone
        // 2.2. mature elements are invisible for not authed users ($user == null)
        // 2.3. mature elements are visible for mature users
        // 2.4. mature elements are visible for not mature users only if they used them

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
        $isGameStart = $options && $options->isGameStart;

        return $this->isVisibleFor($user)
            && ($this->isNeutral() || !$isGameStart)
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

    public function isDisabledByOverride(): bool
    {
        return $this->isDisabled()
            && $this->hasScopeOverride();
    }

    public function isDisabled(): bool
    {
        return $this->scope == Scope::DISABLED;
    }

    public function isInactive(): bool
    {
        return $this->scope == Scope::INACTIVE;
    }

    public function isPrivate(): bool
    {
        return $this->scope == Scope::PRIVATE;
    }

    public function isPublic(): bool
    {
        return $this->scope == Scope::PUBLIC;
    }

    public function isCommon(): bool
    {
        return $this->scope == Scope::COMMON;
    }

    /**
     * Is the scope one of the (fuzzy) disabled ones.
     */
    public function isFuzzyDisabled(): bool
    {
        return Scope::isFuzzyDisabled($this->scope);
    }

    /**
     * Is the scope one of the (fuzzy) public ones.
     */
    public function isFuzzyPublic(): bool
    {
        return Scope::isFuzzyPublic($this->scope);
    }

    public function isNeutral(): bool
    {
        return $this->severity == Severity::NEUTRAL;
    }

    public function isOffending(): bool
    {
        return $this->severity == Severity::OFFENDING;
    }

    public function isMature(): bool
    {
        return $this->severity == Severity::MATURE;
    }

    public function scopeUpdatedAtIso(): ?string
    {
        return self::toIso($this->scopeUpdatedAt);
    }

    public function severityUpdatedAtIso(): ?string
    {
        return self::toIso($this->severityUpdatedAt);
    }

    /**
     * Returns `true` if the element has an override AND
     * that override has some actual changes (is not empty).
     */
    public function hasActualOverride(bool $suppressMeta = false): bool
    {
        $has = $suppressMeta
            ? null
            : $this->getMetaValue(self::META_HAS_ACTUAL_OVERRIDE);

        return $has ?? ($this->hasOverride() && $this->override()->isNotEmpty());
    }

    public function hasScopeOverride(): bool
    {
        return $this->scopeOverride() !== null;
    }

    public function scopeOverride(): ?int
    {
        return $this->hasOverride()
            ? $this->override()->scope
            : null;
    }

    public function hasSeverityOverride(): bool
    {
        return $this->severityOverride() !== null;
    }

    public function severityOverride(): ?int
    {
        return $this->hasOverride()
            ? $this->override()->severity
            : null;
    }

    abstract public function override(): ?Override;

    public function hasOverride(): bool
    {
        return $this->override() !== null;
    }

    // serialization

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'scope' => $this->scope,
            'scope_name' => Scope::getName(intval($this->scope)),
            'severity' => $this->severity,
            'severity_name' => Severity::getName(intval($this->severity)),
            'approved' => $this->isFuzzyPublic(),
            'mature' => $this->isMature(),
            'disabled' => $this->isDisabled(),
            'url' => $this->url(),
            'language' => $this->language()->serialize(),
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
            'updated_at' => $this->updatedAtIso(),
        ];
    }
}
