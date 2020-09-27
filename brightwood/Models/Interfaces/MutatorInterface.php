<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MutatorInterface
{
    function withMutator(callable $mutator) : self;
    function mutate(StoryData $data) : StoryData;

    /**
     * Alias for withMutator().
     */
    function do(callable $mutator) : self;
}
