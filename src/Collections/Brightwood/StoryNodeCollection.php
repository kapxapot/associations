<?php

namespace App\Collections\Brightwood;

use App\Models\Brightwood\Nodes\StoryNode;
use Plasticode\Collections\Basic\TypedCollection;

class StoryNodeCollection extends TypedCollection
{
    protected string $class = StoryNode::class;
}
