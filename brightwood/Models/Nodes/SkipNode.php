<?php

namespace Brightwood\Models\Nodes;

class SkipNode extends RedirectNode
{
    public function __construct(
        int $id,
        string $text,
        int $redirectId
    )
    {
        parent::__construct(
            $id,
            $text,
            [
                $redirectId => null
            ]
        );
    }
}
