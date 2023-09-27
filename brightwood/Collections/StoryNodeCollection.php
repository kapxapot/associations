<?php

namespace Brightwood\Collections;

use Brightwood\Models\Nodes\AbstractStoryNode;
use Plasticode\Collections\Generic\TypedCollection;

class StoryNodeCollection extends TypedCollection
{
    protected string $class = AbstractStoryNode::class;
}
