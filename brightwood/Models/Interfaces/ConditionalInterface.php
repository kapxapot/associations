<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Data\StoryData;

interface ConditionalInterface
{
    public function withCondition(callable $condition): self;

    public function satisfies(?StoryData $data): bool;

    /**
     * Alias for withCondition().
     */
    public function if(callable $condition): self;
}
