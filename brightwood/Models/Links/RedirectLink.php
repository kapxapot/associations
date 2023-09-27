<?php

namespace Brightwood\Models\Links;

use Webmozart\Assert\Assert;

class RedirectLink extends StoryLink
{
    const DEFAULT_WEIGHT = 1;

    private float $weight;

    public function __construct(int $nodeId, float $weight = self::DEFAULT_WEIGHT)
    {
        parent::__construct($nodeId);

        Assert::greaterThan($weight, 0);

        $this->weight = $weight;
    }

    public function weight(): float
    {
        return $this->weight;
    }
}
