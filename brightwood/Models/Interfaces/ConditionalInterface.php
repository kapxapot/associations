<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Data\StoryData;

interface ConditionalInterface
{
    function withCondition(callable $condition) : self;
    function satisfies(?StoryData $data) : bool;

    /**
     * Alias for withCondition().
     */
    function if(callable $condition) : self;
}
