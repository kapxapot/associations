<?php

namespace Brightwood\Models\Traits;

use Brightwood\Models\Data\StoryData;

trait Mutator
{
    /** @var callable|null */
    protected $mutator = null;

    /**
     * @return $this
     */
    public function withMutator(callable $mutator): self
    {
        $this->mutator = $mutator;

        return $this;
    }

    public function mutate(StoryData $data): StoryData
    {
        return $this->mutator
            ? ($this->mutator)($data)
            : $data;
    }

    /**
     * @return $this
     */
    public function does(callable $mutator): self
    {
        return $this->withMutator($mutator);
    }
}
