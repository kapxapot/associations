<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MutatorInterface
{
    /**
     * @return $this
     */
    public function withMutator(callable $mutator): self;

    public function mutate(StoryData $data): StoryData;

    /**
     * Alias for withMutator().
     */
    public function do(callable $mutator): self;
}
