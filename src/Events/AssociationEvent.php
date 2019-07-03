<?php

namespace App\Events;

use Plasticode\Events\Event;

use App\Models\Association;

abstract class AssociationEvent extends Event
{
    private $association;

    public function __construct(Association $association, Event $parent = null)
    {
        parent::__construct($parent);

        $this->association = $association;
    }

    public function getAssociation() : Association
    {
        return $this->association;
    }
}
