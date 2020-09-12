<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Moves\Actions\Action;
use Plasticode\Collections\Basic\TypedCollection;

class ActionCollection extends TypedCollection
{
    protected string $class = Action::class;
}
