<?php

namespace Brightwood\Models\Links;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\ConditionalInterface;
use Brightwood\Models\Interfaces\MutatorInterface;
use Brightwood\Models\Traits\Mutator;

/**
 * @method $this withMutator(callable $mutator)
 * @method $this do(callable $mutator)
 */
abstract class StoryLink implements ConditionalInterface, MutatorInterface
{
    use Mutator;

    protected int $nodeId;

    /** @var callable|null */
    private $condition = null;

    public function __construct(int $nodeId)
    {
        $this->nodeId = $nodeId;
    }

    public function nodeId(): int
    {
        return $this->nodeId;
    }

    /**
     * @return $this
     */
    public function withCondition(callable $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function satisfies(?StoryData $data): bool
    {
        if (!$data || !$this->condition) {
            return true;
        }

        return ($this->condition)($data);
    }

    /**
     * Alias for withCondition().
     */
    public function if(callable $condition): self
    {
        return $this->withCondition($condition);
    }
}
