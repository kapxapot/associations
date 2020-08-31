<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Links\RedirectLink;

class SimpleRedirectNode extends RedirectNode
{
    /**
     * @param string[] $text
     * @param array<int, float|null> $links NodeId -> Weight
     */
    public function __construct(
        int $id,
        array $text,
        array $links
    )
    {
        parent::__construct(
            $id,
            $text,
            array_map(
                fn (int $nodeId, ?float $weight) => new RedirectLink($nodeId, $weight),
                array_keys($links),
                $links
            )
        );
    }
}
