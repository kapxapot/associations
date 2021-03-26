<?php

namespace App\Events\Definition;

use App\Models\Definition;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

abstract class DefinitionEvent extends EntityEvent
{
    protected Definition $definition;

    public function __construct(Definition $definition, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->definition = $definition;
    }

    public function getDefinition(): Definition
    {
        return $this->definition;
    }

    public function getEntity(): Definition
    {
        return $this->getDefinition();
    }
}
