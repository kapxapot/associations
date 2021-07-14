<?php

namespace App\Specifications\Rules\Association;

use App\Models\Association;

/**
 * Association is disabled if it has an override that's disabled.
 */
class AssociationDisabledByOverride extends AbstractAssociationRule
{
    public function checkAssociation(Association $association): bool
    {
        return $association->hasOverride()
            ? $association->override()->isDisabled()
            : false;
    }
}
