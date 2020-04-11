<?php

namespace App\Events;

use App\Models\Association;
use Plasticode\Events\Event;

abstract class AssociationEvent extends Event
{
    protected Association $association;

    public function __construct(Association $association, Event $parent = null)
    {
        parent::__construct($parent);

        $this->association = $association;
    }

    public function getAssociation() : Association
    {
        return $this->association;
    }

    public function getEntity() : Association
    {
        return $this->getAssociation();
    }
}
