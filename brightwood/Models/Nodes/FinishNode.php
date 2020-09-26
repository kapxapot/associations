<?php

namespace Brightwood\Models\Nodes;

class FinishNode extends TextNode
{
    public function isFinish() : bool
    {
        return true;
    }
}
