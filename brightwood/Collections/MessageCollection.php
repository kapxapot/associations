<?php

namespace Brightwood\Collections;

use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Plasticode\Collections\Basic\TypedCollection;

class MessageCollection extends TypedCollection
{
    protected string $class = MessageInterface::class;
}
