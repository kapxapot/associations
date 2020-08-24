<?php

namespace Brightwood\Models\Nodes;

class FinishNode extends StoryNode
{
    public function isFinish() : bool
    {
        return true;
    }
}
