<?php

namespace App\Models;

/**
 * @property integer $associationId
 * @method Association association()
 * @method static withAssociation(Association|callable $association)
 */
class AssociationOverride extends Override
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'association',
        ];
    }
}
