<?php

namespace App\Models\Brightwood\Nodes;

use App\Models\Brightwood\Links\RedirectLink;

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
