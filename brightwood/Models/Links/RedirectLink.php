<?php

namespace Brightwood\Models\Links;

use Webmozart\Assert\Assert;

class RedirectLink extends StoryLink
{
    const DEFAULT_WEIGHT = 1;

    private float $weight;

    public function __construct(int $nodeId, float $weight = null)
    {
        parent::__construct($nodeId);

        $this->weight = $weight ?? self::DEFAULT_WEIGHT;

        Assert::greaterThan($weight, 0);
    }

    public function weight(): float
    {
        return $this->weight;
    }
}
