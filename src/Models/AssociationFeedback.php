<?php

namespace App\Models;

/**
 * @property integer $associationId
 * @method Association association()
 * @method static withAssociation(Association|callable $association)
 */
class AssociationFeedback extends Feedback
{
    protected function requiredWiths() : array
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
            'dislike' => $this->dislike,
            'mature' => $this->mature,
            'creator' => $this->creator()->serializePublic(),
            'created_at' => $this->createdAtIso(),
        ];
    }
}
