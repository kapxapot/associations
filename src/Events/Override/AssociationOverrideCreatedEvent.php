<?php

namespace App\Events\Override;

use App\Models\AssociationOverride;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

class AssociationOverrideCreatedEvent extends EntityEvent
{
    protected AssociationOverride $associationOverride;

    public function __construct(
        AssociationOverride $associationOverride,
        ?Event $parent = null
    )
    {
        parent::__construct($parent);

        $this->associationOverride = $associationOverride;
    }

    public function getEntity(): AssociationOverride
    {
        return $this->getAssociationOverride();
    }

    public function getAssociationOverride(): AssociationOverride
    {
        return $this->associationOverride;
    }
}
