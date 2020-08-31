<?php

namespace Brightwood\Models\Nodes;

class SkipNode extends SimpleRedirectNode
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
