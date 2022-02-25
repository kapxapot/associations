<?php

namespace App\Collections;

use App\Models\AggregatedAssociation;

class AggregatedAssociationCollection extends AssociationCollection
{
    protected string $class = AggregatedAssociation::class;

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
