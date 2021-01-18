<?php

namespace Brightwood\Collections;

use Brightwood\Models\Command;
use Plasticode\Collections\Generic\TypedCollection;

class CommandCollection extends TypedCollection
{
    protected string $class = Command::class;
}
