<?php

namespace Brightwood\Collections;

use Brightwood\Models\Nodes\StoryNode;
use Plasticode\Collections\Basic\TypedCollection;

class StoryNodeCollection extends TypedCollection
{
    protected string $class = StoryNode::class;
}
