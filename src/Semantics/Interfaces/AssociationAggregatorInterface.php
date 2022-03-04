<?php

namespace App\Semantics\Interfaces;

use App\Collections\AggregatedAssociationCollection;
use App\Models\Word;

interface AssociationAggregatorInterface
{
    /**
     * Gathers and aggregates word's associations from 3 sources:
     *
     * - Own associations.
     * - Aggregated associations from dependent words.
     * - Aggregated associations from main word (if relation type allows it).
     */
    public function aggregateFor(Word $word): AggregatedAssociationCollection;
}
