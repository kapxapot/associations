<?php

namespace App\Collections\Brightwood;

use App\Models\Brightwood\Stories\Story;
use Plasticode\Collections\Basic\TypedCollection;

class StoryCollection extends TypedCollection
{
    protected string $class = Story::class;
}
