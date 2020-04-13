<?php

namespace App\Models;

/**
 * @property integer $associationId
 * @method Association association()
 * @method self withAssociation(Association|callable $association)
 */
class AssociationFeedback extends Feedback
{
    protected function requiredWiths(): array
    {
        return [...parent::requiredWiths(), 'association'];
    }
}
