<?php

namespace App\Semantics\Association;

use App\Collections\AggregatedAssociationCollection;
use App\Models\AggregatedAssociation;
use App\Models\Word;
use Webmozart\Assert\Assert;

/**
 * Congregates aggregated associations excluding the duplicates based on algorithm.
 */
class AssociationCongregator
{
    /**
     * Removes semantically duplicate associations.
     *
     * Works with every scope group separately.
     *
     * @param Word $word The word based on which the associations are aggregated.
     */
    public function congregate(
        AggregatedAssociationCollection $col,
        Word $word
    ): AggregatedAssociationCollection
    {
        return AggregatedAssociationCollection::from(
            $col
                ->segregateByScope()
                ->flatMap(
                    fn (AggregatedAssociationCollection $c) => $this->tidy($c, $word)
                )
        );
    }

    /**
     * Removes semantically duplicate associations.
     *
     * 1. If there's an original association with the same destination, all the others are removed.
     * 2. Semantically duplicate associations are like: [any word -> word2], [any word -> word2's main word]. In this case the second association stays, the first goes away.
     *
     * @param Word $word The word based on which the associations are aggregated.
     */
    public function tidy(
        AggregatedAssociationCollection $col,
        Word $word
    ): AggregatedAssociationCollection
    {
        $canonicalGroups = $col->group(
            fn (AggregatedAssociation $a) => (string)$a->otherThanAnchor()->canonical()
        );

        $result = AggregatedAssociationCollection::make();

        foreach ($canonicalGroups as $key => $associations) {
            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                if (!$association->otherThanAnchor()->isCanonical()) {
                    $association->addToLog('Canonical: ' . $key);
                }
            }

            // no need to choose in case of one association
            if ($associations->count() === 1) {
                $result = $result->add($associations->first());

                continue;
            }

            // choose the best association in group
            //
            // 1. choose by other than anchor word closest to canonical
            // 2. if there's an original association, prefer it, otherwise doesn't matter

            /** @var int|null $minDistance */
            $minDistance = null;
            $minAssociations = AggregatedAssociationCollection::empty();

            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                $distance = $association
                    ->otherThanAnchor()
                    ->distanceFromCanonical();

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                    $minAssociations = AggregatedAssociationCollection::collect($association);

                    continue;
                }

                if ($distance === $minDistance) {
                    $minAssociations = $minAssociations->add($association);
                }
            }

            /** @var AggregatedAssociation $minAssociation */
            $minAssociation = $minAssociations->first(
                fn (AggregatedAssociation $a) => $a->anchorEquals($word)
            );

            /** @var AggregatedAssociation $best */
            $best = $minAssociation ?? $minAssociations->first();

            Assert::notNull($best);

            $result = $result->add($best);

            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                if ($association->equals($best)) {
                    $association->addToLog('Best');

                    continue;
                }

                $association->addToLog('Best: ' . $best);
            }
        }

        return $result;
    }
}
