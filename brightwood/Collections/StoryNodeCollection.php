<?php

namespace Brightwood\Collections;

use Brightwood\Models\Nodes\AbstractStoryNode;
use Plasticode\Collections\Generic\EquatableCollection;

class StoryNodeCollection extends EquatableCollection
{
    protected string $class = AbstractStoryNode::class;
}
