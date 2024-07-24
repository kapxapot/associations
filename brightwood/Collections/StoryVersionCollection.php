<?php

namespace Brightwood\Collections;

use Brightwood\Models\StoryVersion;
use Plasticode\Collections\Generic\DbModelCollection;

class StoryVersionCollection extends DbModelCollection
{
    protected string $class = StoryVersion::class;
}
