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
        $canonicalGroups = $this->group(
            function (AggregatedAssociation $a) {
                $word = $a->otherThanAnchor();
                $canonical = $word->canonical();

                return $canonical
                    ? $canonical->getId()
                    : 0;
            }
        );

        $result = static::make();

        foreach ($canonicalGroups as $key => $associations) {
            if ($key === 0) {
                continue;
            }

            if ($associations->count() === 1) {
                $result = $result->concat($associations);
                continue;
            }

            // choose the best association in group
            //
            // плавник - рыба <- the best
            // плавник - рыбы
            // плавники - рыба
            // плавники - рыбы
            //
            // 1. choose by other than anchor word closest to canonical
            // 2. if there's an original association, prefer it, otherwise doesn't matter
            //
            // todo: use the closest to canonical

            /** @var integer|null $minDistance */
            $minDistance = null;
            $minDistanceAssociations = null;

            /** @var AggregatedAssociation $association */
            foreach ($associations as $association) {
                $distance = $association
                    ->otherThanAnchor()
                    ->distanceFromCanonical();

                // invalid ancestor
                if ($distance === null) {
                    continue;
                }

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                    $minDistanceAssociations = static::collect($association);
                }

                if ($distance === $minDistance) {
                    $minDistanceAssociations = $minDistanceAssociations->add($association);
                }
            }

            if ($minDistanceAssociations === null || $minDistanceAssociations->isEmpty()) {
                continue;
            }

            $original = $minDistanceAssociations->first(
                fn (AggregatedAssociation $a) => $a->anchor()->equals($originalWord)
            );

            $best = $original ?? $minDistanceAssociations->first();

            // add the best association
            $result = $result->add($best);
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
