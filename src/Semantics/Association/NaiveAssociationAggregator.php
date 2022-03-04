<?php

namespace App\Semantics\Association;

use App\Collections\AggregatedAssociationCollection;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;

/**
 * Aggregates word's associations "on the fly" without any pre-calculations.
 */
class NaiveAssociationAggregator extends AbstractAssociationAggregator
{
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
}
