<?php

namespace Brightwood\Collections;

use Brightwood\Models\StoryStatus;
use Plasticode\Collections\Basic\DbModelCollection;

class StoryStatusCollection extends DbModelCollection
{
    protected string $class = StoryStatus::class;
}
