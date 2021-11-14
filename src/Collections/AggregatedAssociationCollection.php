<?php

namespace App\Collections;

use App\Models\AggregatedAssociation;
use App\Models\User;
use App\Models\Word;

class AggregatedAssociationCollection extends AssociationCollection
{
    protected string $class = AggregatedAssociation::class;

    /**
     * Removes semantically duplicate associations.
     *
     * Works with every scope group separately.
     *
     * @param Word $originalWord The word based on which the associations are aggregated.
     * @param User|null $user The user to congregate against.
     * @return static
     */
    public function congregate(Word $originalWord): self
    {
        return static::from(
            $this
                ->segregateByScope()
                ->flatMap(
                    fn (self $g) => $g->tidy($originalWord)
                )
        );
    }

    /**
     * Removes semantically duplicate associations.
     *
     * 1. If there's a original association with the same destination, all the others are removed.
     * 2. Semantically duplicate associations are like: [any word -> word2], [any word -> word2's main word]. In this case the second association stays, the first goes away.
     *
     * @param Word $originalWord The word based on which the associations are aggregated.
     * @return static
     */
    public function tidy(Word $originalWord): self
    {
        // group by other than anchor canonical word id
        $canonicalGroups = $this->group(
            fn (AggregatedAssociation $a) =>
                '[' . $a->otherThanAnchor()->canonical()->getId() . '] ' . $a->otherThanAnchor()->canonical()->word
        );

        $result = static::make();

        foreach ($canonicalGroups as $key => $associations) {
            foreach ($associations as $association) {
                $association->addToLog('Canonical key is ' . $key);
            }

            // no need to choose in case of one association
            if ($associations->count() === 1) {
                $result = $result->concat($associations);
                continue;
            }

            // choose the best association in group
            //
            // 1. choose by other than anchor word closest to canonical
            // 2. if there's an original association, prefer it, otherwise doesn't matter
            //
            // todo: use the closest to canonical

            /** @var integer|null $minDistance */
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
                }

                if ($distance === $minDistance) {
                    $minAssociations = $minAssociations->add($association);
                }
            }

            // this shouldn't be, but...
            if ($minAssociations->isEmpty()) {
                continue;
            }

            $original = $minAssociations->first(
                fn (AggregatedAssociation $a) => $a->anchor()->equals($originalWord)
            );

            $best = $original ?? $minAssociations->first();

            $result = $result->add($best);

            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                if ($association->equals($best)) {
                    continue;
                }

                $association->addToLog('Because [' . $best->getId() . '] ' . $best->fullName() . ' is the best');
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
