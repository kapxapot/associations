<?php

namespace App\Models;

use App\Collections\AssociationFeedbackCollection;
use App\Collections\AssociationOverrideCollection;
use App\Collections\UserCollection;
use App\Collections\WordCollection;
use App\Models\Interfaces\AssociationInterface;

/**
 * @property integer $firstWordId
 * @property integer $secondWordId
 * @method AssociationInterface canonical()
 * @method AssociationInterface|null canonicalForMe()
 * @method Word firstWord()
 * @method Word secondWord()
 * @method static withCanonical(AssociationInterface|callable $canonical)
 * @method static withCanonicalForMe(AssociationInterface|callable|null $canonicalForMe)
 * @method static withFeedbacks(AssociationFeedbackCollection|callable $feedbacks)
 * @method static withFirstWord(Word|callable $firstWord)
 * @method static withOverrides(AssociationOverrideCollection|callable $overrides)
 * @method static withSecondWord(Word|callable $secondWord)
 */
class Association extends LanguageElement implements AssociationInterface
{
    const DEFAULT_SIGN = '→';
    const APPROVED_SIGN = '⇉';

    private string $canonicalPropertyName = 'canonical';

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            $this->canonicalPropertyName,
            'canonicalForMe',
            'firstWord',
            'secondWord',
        ];
    }

    public function isVisibleFor(?User $user): bool
    {
        return ($user && $user->policy()->canSeeAllAssociations())
            ? true
            : parent::isVisibleFor($user);
    }

    public function words(): WordCollection
    {
        return WordCollection::collect(
            $this->firstWord(),
            $this->secondWord()
        );
    }

    /**
     * The association is canonical if its words are canonical.
     */
    public function isCanonical(): bool
    {
        return $this->firstWord()->isCanonical()
            && $this->secondWord()->isCanonical();
    }

    public function hasWord(Word $word): bool
    {
        return $this->words()->contains($word);
    }

    public function hasWords(Word $first, Word $second): bool
    {
        // the same word is NO
        if ($first->equals($second)) {
            return false;
        }

        $ids = $this->words()->ids();

        return $ids->contains($first->getId()) && $ids->contains($second->getId());
    }

    public function feedbacks(): AssociationFeedbackCollection
    {
        return AssociationFeedbackCollection::from(
            parent::feedbacks()
        );
    }

    public function overrides(): AssociationOverrideCollection
    {
        return AssociationOverrideCollection::from(
            parent::overrides()
        );
    }

    public function dislikes(): AssociationFeedbackCollection
    {
        return $this->feedbacks()->dislikes();
    }

    public function matures(): AssociationFeedbackCollection
    {
        return $this->feedbacks()->matures();
    }

    public function feedbackBy(User $user): ?AssociationFeedback
    {
        return $this->feedbacks()->firstBy($user);
    }

    public function feedbackByMe(): ?AssociationFeedback
    {
        return $this->me()
            ? $this->feedbackBy($this->me())
            : null;
    }

    public function isDisabledByOverride(): bool
    {
        return parent::isDisabledByOverride()
            || $this->isSelfRelated()
            || $this->words()->any(
                fn (Word $w) => $w->isDisabledByOverride()
            );
    }

    public function override(): ?AssociationOverride
    {
        return $this->overrides()->latest();
    }

    /**
     * Returns one of the association's words different from the provided one.
     */
    public function otherWord(Word $word): Word
    {
        return $this->firstWord()->equals($word)
            ? $this->secondWord()
            : $this->firstWord();
    }

    /**
     * Users that used this association.
     */
    public function users(): UserCollection
    {
        return $this->turns()->users();
    }

    /**
     * Users + creator (in case when turns are deleted/lost).
     */
    public function extendedUsers(): UserCollection
    {
        return $this
            ->users()
            ->add($this->creator())
            ->distinct();
    }

    public function fullName(): string
    {
        return
            $this->firstWord()->word . ' ' .
            $this->sign() . ' ' .
            $this->secondWord()->word;
    }

    public function sign(): string
    {
        return $this->isFuzzyPublic()
            ? self::APPROVED_SIGN
            : self::DEFAULT_SIGN;
    }

    /**
     * For unknown reason Twig can't read 'canonical' fake method.
     */
    public function canonical(): AssociationInterface
    {
        return $this->getWithProperty(
            $this->canonicalPropertyName
        );
    }

    public function minWordScope(): int
    {
        return $this
            ->words()
            ->numerize(
                fn (Word $w) => intval($w->scope)
            )
            ->min();
    }

    public function maxWordSeverity(): int
    {
        return $this
            ->words()
            ->numerize(
                fn (Word $w) => intval($w->severity)
            )
            ->max();
    }

    public function hasAllGoodPartsOfSpeech(): bool
    {
        return $this->firstWord()->isGoodPartOfSpeech()
            && $this->secondWord()->isGoodPartOfSpeech();
    }

    /**
     * Returns unique association key in format '[first word id]:[second word id]'.
     */
    public function key(): string
    {
        return $this->firstWordId . ':' . $this->secondWordId;
    }

    /**
     * Checks if the association's words are related to each other.
     * Thus making the association invalid.
     */
    public function isSelfRelated(): bool
    {
        return $this->firstWord()->isCanonicallyRelatedTo(
            $this->secondWord()
        );
    }

    // AssociationInterface

    public function getFirstWord(): Word
    {
        return $this->firstWord();
    }

    public function getSecondWord(): Word
    {
        return $this->secondWord();
    }

    public function isReal(): bool
    {
        return $this->isPersisted();
    }

    public function toReal(): ?Association
    {
        return $this;
    }

    public function isValid(): bool
    {
        return true;
    }

    // serialization

    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'name' => $this->fullName(),
            ]
        );
    }
}
