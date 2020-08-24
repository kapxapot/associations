<?php

namespace Brightwood\Collections;

use Brightwood\Models\Links\StoryLink;
use Plasticode\Collections\Basic\TypedCollection;

class StoryLinkCollection extends TypedCollection
{
    protected string $class = StoryLink::class;
}
