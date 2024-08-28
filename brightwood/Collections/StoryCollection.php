<?php

namespace Brightwood\Collections;

use Brightwood\Models\Stories\Core\Story;
use Plasticode\Collections\Generic\DbModelCollection;

class StoryCollection extends DbModelCollection
{
    protected string $class = Story::class;
}
