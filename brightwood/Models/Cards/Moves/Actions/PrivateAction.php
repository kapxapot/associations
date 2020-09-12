<?php

namespace Brightwood\Models\Cards\Moves\Actions;

abstract class MessageAction extends Action
{
    abstract public function message() : string;
}
