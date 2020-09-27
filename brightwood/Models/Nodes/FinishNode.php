<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;

class FinishNode extends StaticNode
{
    public function isFinish(?StoryData $data) : bool
    {
        return true;
    }
}
