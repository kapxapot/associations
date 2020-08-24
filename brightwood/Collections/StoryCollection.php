<?php

namespace Brightwood\Collections;

use Brightwood\Models\Stories\Story;
use Plasticode\Collections\Basic\TypedCollection;

class StoryCollection extends TypedCollection
{
    protected string $class = Story::class;
}
