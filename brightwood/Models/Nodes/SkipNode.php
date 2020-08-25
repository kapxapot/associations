<?php

namespace Brightwood\Models\Nodes;

class SkipNode extends RedirectNode
{
    /**
     * @param string[] $text
     */
    public function __construct(
        int $id,
        array $text,
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
