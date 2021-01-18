<?php

namespace Brightwood\Collections;

use Brightwood\Models\StoryStatus;
use Plasticode\Collections\Generic\DbModelCollection;

class StoryStatusCollection extends DbModelCollection
{
    protected string $class = StoryStatus::class;
}
