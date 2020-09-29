<?php

namespace Brightwood\Collections;

use Brightwood\Models\Command;
use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Collections\Basic\TypedCollection;

class CommandCollection extends TypedCollection
{
    protected string $class = Command::class;

    /**
     * Don't confuse with stringify!
     */
    public function stringize() : ScalarCollection
    {
        return $this->scalarize(
            fn (Command $c) => $c->toString()
        );
    }
}
