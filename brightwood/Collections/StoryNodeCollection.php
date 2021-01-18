<?php

namespace Brightwood\Collections;

use Brightwood\Models\Nodes\StoryNode;
use Plasticode\Collections\Generic\TypedCollection;

class StoryNodeCollection extends TypedCollection
{
    protected string $class = StoryNode::class;
}
