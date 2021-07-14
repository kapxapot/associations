<?php

namespace App\Specifications\Rules\Association;

use App\Models\Association;
use App\Models\Word;

/**
 * Association is disabled if any of its words is disabled.
 */
class AssociationDisabledByWords extends AbstractAssociationRule
{
    public function checkAssociation(Association $association): bool
    {
        return $association->words()->any(
            fn (Word $w) => $w->isDisabled()
        );
    }
}
