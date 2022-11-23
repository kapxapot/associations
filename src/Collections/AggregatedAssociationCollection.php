<?php

namespace App\Collections;

use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;

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

    /**
     * Returns the embedded associations.
     */
    public function associations(): AssociationCollection
    {
        return AssociationCollection::from(
            $this->map(
                fn (AggregatedAssociation $aa) => $aa->association()
            )
        );
    }

    /**
     * Returns the embedded associations having the provided word.
     */
    public function distillByWord(Word $word): AssociationCollection
    {
        return $this
            ->associations()
            ->where(
                fn (Association $a) => $a->hasWord($word)
            );
    }

    public function sortByOtherThanAnchor(): self
    {
        return $this->ascStr(
            fn (AggregatedAssociation $aa) => $aa->otherThanAnchor()->word
        );
    }
}
