<?php

namespace App\Specifications\Rules\Association;

use App\Models\Association;

/**
 * Association is disabled if its words are **canonically** related.
 */
class AssociationDisabledByWordsRelation extends AbstractAssociationRule
{
    public function checkAssociation(Association $association): bool
    {
        return $association->firstWord()->isCanonicallyRelatedTo(
            $association->secondWord()
        );
    }
}
