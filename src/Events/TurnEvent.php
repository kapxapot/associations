<?php

namespace App\Events;

use App\Models\Turn;
use Plasticode\Events\Event;

abstract class TurnEvent extends Event
{
    protected Turn $turn;

    public function __construct(Turn $turn, Event $parent = null)
    {
        parent::__construct($parent);

        $this->turn = $turn;
    }

    public function getTurn() : Turn
    {
        return $this->turn;
    }

    public function getEntity() : Turn
    {
        return $this->getTurn();
    }
}
