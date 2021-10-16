<?php

namespace App\Models;

use App\Collections\AssociationCollection;
use App\Collections\MetaAssociationCollection;
use App\Collections\PartOfSpeechCollection;
use App\Collections\WordCollection;
use App\Collections\WordFeedbackCollection;
use App\Collections\WordOverrideCollection;
use App\Collections\WordRelationCollection;
use App\Models\DTO\MetaAssociation;
use App\Models\Interfaces\DictWordInterface;
use App\Semantics\Definition\DefinitionAggregate;
use App\Semantics\Interfaces\PartOfSpeechableInterface;

/**
 * @property integer|null $mainId
 * @property string|null $originalWord
 * @property string $word
 * @property string|null $wordUpdatedAt
 * @method AssociationCollection associations()
 * @method WordRelationCollection counterRelations()
 * @method Definition|null definition()
 * @method WordCollection dependents()
 * @method DictWordInterface|null dictWord()
 * @method Word|null main()
 * @method DefinitionAggregate|null parsedDefinition()
 * @method WordRelationCollection relations()
 * @method static withAssociations(AssociationCollection|callable $associations)
 * @method static withCounterRelations(WordRelationCollection|callable $counterRelations)
 * @method static withDefinition(Definition|callable|null $definition)
 * @method static withDependents(WordCollection|callable $dependents)
 * @method static withDictWord(DictWordInterface|callable|null $dictWord)
 * @method static withFeedbacks(WordFeedbackCollection|callable $feedbacks)
 * @method static withMain(Word|callable|null $main)
 * @method static withOverrides(WordOverrideCollection|callable $overrides)
 * @method static withParsedDefinition(DefinitionAggregate|callable|null $parsedDefinition)
 * @method static withRelations(WordRelationCollection|callable $relations)
 */
class Word extends LanguageElement implements PartOfSpeechableInterface
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'associations',
            'counterRelations',
            'definition',
            'dictWord',
            'main',
            'parsedDefinition',
            'relations'
        ];
    }

    public function isVisibleFor(?User $user): bool
    {
        return ($user && $user->policy()->canSeeAllWords())
            ? true
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

    public function isDisabledByOverride(): bool
    {
        return parent::isDisabledByOverride()
            || $this->hasMain() && $this->main()->isDisabledByOverride();
    }

    public function associationByWord(self $word): ?Association
    {
        return $this
            ->associations()
            ->first(
                fn (Association $a) => $a->otherWord($this)->equals($word)
            );
    }

    /**
     * Returns approved associations visible for *the current user*.
     */
    public function approvedVisibleAssociations(): AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->visibleFor($this->me());
    }

    /**
     * Returns approved associations invisible for *the current user*.
     */
    public function approvedInvisibleAssociations(): AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->invisibleFor($this->me());
    }

    /**
     * Returns not approved associations visible for *the current user*.
     */
    public function notApprovedVisibleAssociations(): AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->visibleFor($this->me());
    }

    /**
     * Returns not approved associations invisible for *the current user*.
     */
    public function notApprovedInvisibleAssociations(): AssociationCollection
    {
        return $this
            ->notApprovedAssociations()
            ->invisibleFor($this->me());
    }

    /**
     * Returns disabled associations visible for *the current user*.
     */
    public function disabledVisibleAssociations(): AssociationCollection
    {
        return $this
            ->disabledAssociations()
            ->visibleFor($this->me());
    }

    /**
     * Returns disabled associations invisible for *the current user*.
     */
    public function disabledInvisibleAssociations(): AssociationCollection
    {
        return $this
            ->disabledAssociations()
            ->invisibleFor($this->me());
    }

    public function approvedAssociations(): AssociationCollection
    {
        return $this
            ->associations()
            ->fuzzyPublic()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    public function notApprovedAssociations(): AssociationCollection
    {
        return $this
            ->associations()
            ->private()
            ->ascStr(
                fn (Association $a) => $a->otherWord($this)->word
            );
    }

    public function disabledAssociations(): AssociationCollection
    {
        return $this
            ->associations()
            ->fuzzyDisabled()
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

    public function isGoodPartOfSpeech(): bool
    {
        return $this->partsOfSpeech()->isAnyGood();
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

        if ($poses->isEmpty() && $this->hasMain()) {
            $poses = $this->main()->partsOfSpeech();
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

    public function primaryRelation(): ?WordRelation
    {
        return $this->relations()->primary();
    }

    /**
     * Checks if the word is "bad" and the canonical word must be used instead.
     */
    public function shouldUseCanonical(): bool
    {
        // use canonical word for disabled words
        return $this->isDisabled() && $this->hasMain();
    }

    /**
     * Checks if the words have the same canonical word.
     */
    public function canonicalEquals(Word $word): bool
    {
        return $this->canonical()->equals(
            $word->canonical()
        );
    }

    /**
     * Returns the most canonical and still playable word.
     *
     * Goes up the canonical chain until stumbles at an unplayable word.
     */
    public function canonicalPlayableAgainst(?User $user): ?self
    {
        if (!$this->isPlayableAgainst($user)) {
            return null;
        }

        $mainPlayable = $this->hasMain()
            ? $this->main()->canonicalPlayableAgainst($user)
            : null;

        return $mainPlayable ?? $this;
    }

    /**
     * Duplicate of `main()` for Twig.
     */
    public function mainWord(): ?self
    {
        return $this->main();
    }

    /**
     * Returns the word's canonical form.
     *
     * - If the word is canonical, returns itself.
     * - Otherwise, returns the root main word (main()->main()->...->main()).
     */
    public function canonical(): self
    {
        return $this->isCanonical()
            ? $this
            : $this->main()->canonical();
    }

    /**
     * The word is canonical if it doesn't have main word.
     */
    public function isCanonical(): bool
    {
        return !$this->hasMain();
    }

    /**
     * Checks if the current word belongs to transitive main words of
     * the `$other` word.
     * 
     * If it belongs, the `$other` can't be added as a main word for the current word,
     * because it will create a cycle.
     * 
     * In case when `$this === $other`, returns `true` as well, because a word
     * mustn't be main to itself.
     */
    public function isTransitiveMainOf(Word $other): bool
    {
        return $other
            ->mainChain()
            ->add($other)
            ->contains($this);
    }

    /**
     * Returns the chain of `main()` words of the current word.
     *
     * @return WordCollection
     */
    public function mainChain(?Word $stopper = null): WordCollection
    {
        return ($this->hasMain() && !$this->equals($stopper))
            ? WordCollection::collect($this->main())
                ->concat(
                    $this->main()->mainChain($stopper ?? $this)
                )
            : WordCollection::empty();
    }

    public function hasMain(): bool
    {
        return $this->main() !== null;
    }

    /**
     * Checks if `$word` is one of directly related words (both ways).
     */
    public function isRelatedTo(Word $word): bool
    {
        return $this->allRelatedWords()->contains($word);
    }

    /**
     * Checks if the words are related to one another by any third word.
     * 
     * A relates to B, C relates to B => A remotely relates to C.
     */
    public function isRemotelyRelatedTo(Word $word): bool
    {
        return $this
            ->relatedCanonicalWords()
            ->intersect($word->relatedCanonicalWords())
            ->any();
    }

    /**
     * Checks if `$word`'s canonical word is one of the canonical related words.
     * 
     * Gets canonical words for all related words and looks for `$word`'s
     * canonical word in them.
     */
    public function isCanonicallyRelatedTo(Word $word): bool
    {
        return $this->allRelatedCanonicalWords()->contains(
            $word->canonical()
        );
    }

    /**
     * Concats relations and counter-relations.
     */
    public function allRelations(): WordRelationCollection
    {
        $out = $this->relations();
        $in = $this->counterRelations();

        return WordRelationCollection::merge($out, $in);
    }

    /**
     * Concats related and counter-related words (distinct).
     */
    public function allRelatedWords(): WordCollection
    {
        $out = $this->relatedWords();
        $in = $this->counterRelatedWords();

        return WordCollection::merge($out, $in)->distinct();
    }

    /**
     * Returns distinct words that the current word is related to.
     * 
     * this -> A
     * this -> B
     */
    public function relatedWords(): WordCollection
    {
        $words = $this->relations()->map(
            fn (WordRelation $wr) => $wr->mainWord()
        );

        return WordCollection::fromDistinct($words);
    }

    /**
     * Returns distinct words that are related to the current word.
     * 
     * A -> this
     * B -> this
     */
    public function counterRelatedWords(): WordCollection
    {
        $words = $this->counterRelations()->map(
            fn (WordRelation $wr) => $wr->word()
        );

        return WordCollection::fromDistinct($words);
    }

    /**
     * Returns distinct canonical words for related words.
     */
    public function relatedCanonicalWords(): WordCollection
    {
        return $this->relatedWords()->canonical()->distinct();
    }

    /**
     * Returns distinct canonical words for duplex related words.
     */
    public function allRelatedCanonicalWords(): WordCollection
    {
        return $this->allRelatedWords()->canonical()->distinct();
    }

    public function wordUpdatedAtIso(): ?string
    {
        return self::toIso($this->wordUpdatedAt);
    }

    // serialization

    public function serialize(): array
    {
        $dw = $this->dictWord();
        $def = $this->definition();

        return array_merge(
            parent::serialize(),
            [
                'word' => $this->word,
                'has_dict_word' => $dw && $dw->isValid(),
                'has_definition' => $def && $def->isValid(),
            ]
        );
    }
}
