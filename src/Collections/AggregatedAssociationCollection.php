<?php

namespace App\Collections;

use App\Models\AggregatedAssociation;
use App\Models\User;
use App\Models\Word;
use Webmozart\Assert\Assert;

class AggregatedAssociationCollection extends AssociationCollection
{
    protected string $class = AggregatedAssociation::class;

    /**
     * Removes semantically duplicate associations.
     *
     * Works with every scope group separately.
     *
     * @param Word $rootWord The word based on which the associations are aggregated.
     * @return static
     */
    public function congregate(Word $rootWord): self
    {
        return static::from(
            $this
                ->segregateByScope()
                ->flatMap(
                    fn (self $g) => $g->tidy($rootWord)
                )
        );
    }

    /**
     * Removes semantically duplicate associations.
     *
     * 1. If there's an original association with the same destination, all the others are removed.
     * 2. Semantically duplicate associations are like: [any word -> word2], [any word -> word2's main word]. In this case the second association stays, the first goes away.
     *
     * @param Word $rootWord The word based on which the associations are aggregated.
     * @return static
     */
    public function tidy(Word $rootWord): self
    {
        $canonicalGroups = $this->group(
            fn (AggregatedAssociation $a) => (string)$a->otherThanAnchor()->canonical()
        );

        $result = static::make();

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
            $minAssociations = static::empty();

            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                $distance = $association
                    ->otherThanAnchor()
                    ->distanceFromCanonical();

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                    $minAssociations = static::collect($association);

                    continue;
                }

                if ($distance === $minDistance) {
                    $minAssociations = $minAssociations->add($association);
                }
            }

            /** @var AggregatedAssociation $rootAssociation */
            $rootAssociation = $minAssociations->first(
                fn (AggregatedAssociation $a) => $a->anchorEquals($rootWord)
            );

            /** @var AggregatedAssociation $best */
            $best = $rootAssociation ?? $minAssociations->first();

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

    /**
     * @return static
     */
    public function notJunky(): self
    {
        return $this->where(
            fn (AggregatedAssociation $a) => !$a->isJunky()
        );
    }
}
