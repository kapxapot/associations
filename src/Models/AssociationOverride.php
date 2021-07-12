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

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'association' => $this->association()->serialize(),
            'approved' => $this->approved,
            'mature' => $this->mature,
            'disabled' => $this->disabled,
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
}
