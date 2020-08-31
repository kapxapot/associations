<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MutatorInterface
{
    function withMutator(callable $func) : self;
    function mutate(?StoryData $data) : ?StoryData;

    /**
     * Alias for withMutator().
     */
    function do(callable $func) : self;
}
