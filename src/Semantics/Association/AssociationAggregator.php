<?php

namespace App\Semantics\Association;

use App\Collections\AggregatedAssociationCollection;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

/**
 * Aggregates word's associations "on the fly" without any pre-calculations.
 */
class AssociationAggregator implements AssociationAggregatorInterface
{
    private AssociationCongregator $congregator;

    public function __construct(
        AssociationCongregator $congregator
    )
    {
        $this->congregator = $congregator;
    }

    public function aggregateForWord(Word $word): AggregatedAssociationCollection
    {
        $col = $this->aggregateAssociations($word);

        $this->markJunky($col, $word);

        return $this->sort($col);
    }

    public function aggregateAssociations(
        Word $word,
        ?Word $exceptWord = null
    ): AggregatedAssociationCollection
    {
        // add dependent words
        $relatedWords = $word->dependents();

        if ($exceptWord !== null) {
            $relatedWords = $relatedWords->except($exceptWord);
        }

        // add main word
        $primaryRelation = $word->primaryRelation();

        if ($primaryRelation && $primaryRelation->isSharingAssociationsDown()) {
            $mainWord = $primaryRelation->mainWord();

            if (!$mainWord->equals($exceptWord)) {
                $relatedWords = $relatedWords->add($mainWord);
            }
        }

        // aggregate associations
        $col = $relatedWords
            ->flatMap(
                fn (Word $rw) => $this
                    ->aggregateAssociations($rw, $word)
                    ->map(
                        fn (AggregatedAssociation $a) => $a->withSoftAnchor($rw)
                    )
            )
            ->concat(
                $word->associations()->map(
                    fn (Association $a) => new AggregatedAssociation($a, $word)
                )
            );

        return AggregatedAssociationCollection::from($col);
    }

    /**
     * Congregates aggregated associations and mark junky ones.
     *
     * Todo: the associations can overlap for different words and this can cause problems (mark them as junky for a specific word)
     */
    public function markJunky(AggregatedAssociationCollection $col, Word $word): void
    {
        $congregated = $this->congregator->congregate($col, $word);

        $junk = $col->except($congregated);

        $junk->apply(
            fn (AggregatedAssociation $a) => $a->markAsJunky()
        );
    }

    /**
     * Orders collection by `other than anchor` word and
     * by distance of `anchor` from its canonical word.
     */
    public function sort(AggregatedAssociationCollection $col): AggregatedAssociationCollection
    {
        return $col->sortBy(
            SortStep::byFunc(
                fn (AggregatedAssociation $a) => $a->otherThanAnchor()->word,
                Sort::STRING
            ),
            SortStep::byFunc(
                fn (AggregatedAssociation $a) => $a->anchor()->distanceFromCanonical()
            )
        );
    }
}
