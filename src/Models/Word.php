<?php

namespace App\Models;

use App\Collections\AssociationCollection;
use App\Collections\MetaAssociationCollection;
use App\Collections\PartOfSpeechCollection;
use App\Collections\WordCollection;
use App\Collections\WordFeedbackCollection;
use App\Collections\WordOverrideCollection;
use App\Models\DTO\MetaAssociation;
use App\Models\Interfaces\DictWordInterface;
use App\Semantics\Definition\DefinitionAggregate;
use App\Semantics\Interfaces\PartOfSpeechableInterface;

/**
 * @property string|null $originalWord
 * @property string|null $tokenizedWord
 * @property string $word
 * @property string|null $wordUpdatedAt
 * @method AssociationCollection associations()
 * @method Definition|null definition()
 * @method DictWordInterface|null dictWord()
 * @method DefinitionAggregate|null parsedDefinition()
 * @method static withAssociations(AssociationCollection|callable $associations)
 * @method static withDefinition(Definition|callable|null $definition)
 * @method static withDictWord(DictWordInterface|callable|null $dictWord)
 * @method static withFeedbacks(WordFeedbackCollection|callable $feedbacks)
 * @method static withOverrides(WordOverrideCollection|callable $overrides)
 * @method static withParsedDefinition(DefinitionAggregate|callable|null $parsedDefinition)
 */
class Word extends LanguageElement implements PartOfSpeechableInterface
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'associations',
            'definition',
            'dictWord',
            'parsedDefinition',
        ];
    }

    public function isVisibleFor(?User $user): bool
    {
        return $this->isDisabled()
            ? $user && ($user->equals($this->creator()) || $user->policy()->canSeeAllWords())
            : parent::isVisibleFor($user);
    }

    public function feedbacks(): WordFeedbackCollection
    {
        return WordFeedbackCollection::from(
            parent::feedbacks()
        );
    }

    public function overrides(): WordOverrideCollection
    {
        return WordOverrideCollection::from(
            parent::overrides()
        );
    }

    public function dislikes(): WordFeedbackCollection
    {
        return $this->feedbacks()->dislikes();
    }

    public function matures(): WordFeedbackCollection
    {
        return $this->feedbacks()->matures();
    }

    public function feedbackBy(User $user): ?WordFeedback
    {
        return $this->feedbacks()->firstBy($user);
    }

    public function feedbackByMe(): ?WordFeedback
    {
        return $this->me()
            ? $this->feedbackBy($this->me())
            : null;
    }

    public function associationByWord(self $word): ?Association
    {
        return $this
            ->associations()
            ->first(
                fn (Association $a) => $a->otherWord($this)->equals($word)
            );
    }

    public function randomPublicAssociation(?self $excludeOtherWord = null): ?Association
    {
        return $this
            ->publicAssociations()
            ->where(
                fn (Association $a) => !$a->otherWord($this)->equals($excludeOtherWord)
            )
            ->random();
    }

    /**
     * Returns approved associations that are visible for everyone (public).
     */
    public function publicAssociations(): AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->visible();
    }

    /**
     * Returns approved associations visible for _the current user_.
     */
    public function approvedVisibleAssociations(): AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->visibleFor($this->me());
    }

    /**
     * Returns approved associations invisible for _the current user_.
     */
    public function approvedInvisibleAssociations(): AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->invisibleFor($this->me());
    }

    /**
     * Returns not approved associations visible for _the current user_.
     */
    public function notApprovedVisibleAssociations(): AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->visibleFor($this->me());
    }

    /**
     * Returns not approved associations invisible for _the current user_.
     */
    public function notApprovedInvisibleAssociations(): AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->invisibleFor($this->me());
    }

    public function approvedAssociations(): AssociationCollection
    {
        return $this
            ->associations()
            ->approved()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    public function notApprovedAssociations(): AssociationCollection
    {
        return $this
            ->associations()
            ->notApproved()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    /**
     * Returns the origin association for this word
     * (if the word originates from an association).
     * 
     * - The oldest association is used as a starting point.
     * - If the other word in the association is older than this one,
     * it is considered as an origin association.
     * - Otherwise, there is no origin association.
     */
    public function originAssociation(): ?Association
    {
        $oldest = $this->associations()->oldest();

        if (is_null($oldest)) {
            return null;
        }

        $otherWord = $oldest->otherWord($this);

        return $this->isNewerThan($otherWord->createdAt)
            ? $oldest
            : null;
    }

    /**
     * If the origin association exists, the other word in it is
     * considered as an origin word.
     */
    public function originWord(): ?self
    {
        $originAssociation = $this->originAssociation();

        return $originAssociation
            ? $originAssociation->otherWord($this)
            : null;
    }

    public function originChain(): MetaAssociationCollection
    {
        $chain = MetaAssociationCollection::empty();
        $originAssociation = $this->originAssociation();

        return $originAssociation
            ? $chain->add(
                new MetaAssociation($originAssociation, $this),
                ...$this->originWord()->originChain()
            )
            : $chain;
    }

    public function associatedWordsFor(User $user): WordCollection
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

    public function serialize(): array
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

    public function proposedTypos(): array
    {
        return $this
            ->feedbacks()
            ->typos()
            ->group(
                fn (WordFeedback $f) => $f->typo
            );
    }

    public function proposedDuplicates(): array
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
    public function typoByMe(): ?string
    {
        $feedback = $this->feedbackByMe();

        return ($feedback && $this->isRealTypo($feedback->typo))
            ? $feedback->typo
            : null;
    }

    /**
     * Checks if the typo is not empty and is different from the word.
     */
    public function isRealTypo(?string $typo): bool
    {
        return strlen($typo) > 0
            && $this->word !== $typo;
    }

    /**
     * Returns the duplicate provided by the current user.
     */
    public function duplicateByMe(): ?self
    {
        $feedback = $this->feedbackByMe();

        return $feedback
            ? $feedback->duplicate()
            : null;
    }

    /**
     * Returns word or typo by the current user with '*' (if any).
     */
    public function displayName(): string
    {
        $typo = $this->typoByMe();

        return is_null($typo)
            ? $this->word
            : $typo . '*';
    }

    /**
     * Returns the original word + typo by the current user.
     */
    public function fullDisplayName(): string
    {
        $name = $this->displayName();

        if ($this->typoByMe() !== null) {
            $name .= ' (' . $this->word . ')';
        }

        return $name;
    }

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        if ($this->hasPartsOfSpeechOverride()) {
            return $this->partsOfSpeechOverride();
        }

        $poses = PartOfSpeechCollection::empty();

        $dw = $this->dictWord();

        if ($dw !== null && $dw->partOfSpeech() !== null) {
            $poses = $poses->add(
                $dw->partOfSpeech()
            );
        }

        if ($this->parsedDefinition() !== null) {
            $poses = $poses->concat(
                $this->parsedDefinition()->partsOfSpeech()
            );
        }

        return $poses->distinct();
    }

    public function override(): ?WordOverride
    {
        return $this->overrides()->latest();
    }

    public function hasPartsOfSpeechOverride(): bool
    {
        return $this->partsOfSpeechOverride() !== null;
    }

    public function partsOfSpeechOverride(): ?PartOfSpeechCollection
    {
        return $this->hasOverride()
            ? $this->override()->partsOfSpeech()
            : null;
    }

    public function wordUpdatedAtIso(): ?string
    {
        return self::toIso($this->wordUpdatedAt);
    }
}
