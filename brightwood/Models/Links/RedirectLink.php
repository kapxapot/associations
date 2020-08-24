<?php

namespace Brightwood\Models\Links;

use Webmozart\Assert\Assert;

class RedirectLink extends StoryLink
{
    private const DEFAULT_WEIGHT = 1;

    private float $weight;

    public function __construct(
        int $nodeId,
        ?float $weight = null
    )
    {
        parent::__construct($nodeId);

        $weight ??= self::DEFAULT_WEIGHT;

        Assert::greaterThan($weight, 0);

        $this->weight = $weight;
    }

    public function weight() : float
    {
        return $this->weight;
    }
}
