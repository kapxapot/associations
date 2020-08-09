<?php

namespace App\Models\Brightwood\Links;

abstract class StoryLink
{
    protected int $nodeId;

    public function __construct(
        int $nodeId
    )
    {
        $this->nodeId = $nodeId;
    }

    public function nodeId() : int
    {
        return $this->nodeId;
    }
}
