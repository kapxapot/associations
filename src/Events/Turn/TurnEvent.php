<?php

namespace App\Events\Turn;

use App\Models\Turn;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

abstract class TurnEvent extends EntityEvent
{
    protected Turn $turn;

    public function __construct(Turn $turn, ?Event $parent = null)
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
