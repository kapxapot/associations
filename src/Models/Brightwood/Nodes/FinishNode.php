<?php

namespace App\Models\Brightwood\Nodes;

class FinishNode extends StoryNode
{
    public function isFinish() : bool
    {
        return true;
    }
}
