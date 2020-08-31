<?php

namespace Brightwood\Models\Links;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\MutatorInterface;

abstract class StoryLink implements MutatorInterface
{
    protected int $nodeId;

    /** @var callable|null */
    private $condition = null;

    /** @var callable|null */
    protected $mutator = null;

    public function __construct(
        int $nodeId
    )
    {
        $this->nodeId = $nodeId;
    }

    public function nodeId() : int
    {
        return $this->nodeId;
    }

    /**
     * @return static
     */
    public function withMutator(callable $func) : self
    {
        $this->mutator = $func;
        return $this;
    }

    public function mutate(?StoryData $data) : ?StoryData
    {
        return ($data && $this->mutator)
            ? ($this->mutator)($data)
            : $data;
    }

    /**
     * @return static
     */
    public function withCondition(callable $condition) : self
    {
        $this->condition = $condition;
        return $this;
    }

    public function satisfies(?StoryData $data) : bool
    {
        if (is_null($data) || is_null($this->condition)) {
            return true;
        }

        return ($this->condition)($data);
    }
}
