<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\MutatorInterface;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Stories\Story;
use Webmozart\Assert\Assert;

abstract class StoryNode implements MutatorInterface
{
    protected ?Story $story = null;
    protected int $id;

    /** @var string[] */
    protected array $text;

    /** @var callable|null */
    protected $mutator = null;

    /**
     * @param string[] $text
     */
    public function __construct(
        int $id,
        array $text
    )
    {
        $this->id = $id;
        $this->text = $text;
    }

    public function id() : int
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function text() : array
    {
        return $this->text;
    }

    /**
     * @return static
     */
    public function withStory(Story $story) : self
    {
        $this->story = $story;
        return $this;
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
     * Alias for withMutator().
     * 
     * @return static
     */
    public function do(callable $func) : self
    {
        return $this->withMutator($func);
    }

    abstract public function isFinish() : bool;

    public function getMessage(?StoryData $data = null) : StoryMessage
    {
        return (new StoryMessage(
            $this->id,
            $this->text
        ))->withData(
            $this->mutate($data)
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
    }

    /**
     * Tries to get a node by id.
     * 
     * Throws an {@see \InvalidArgumentException} if the node is not found.
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveNode(int $id) : self
    {
        $node = $this->story->getNode($id);

        Assert::notNull($node);

        return $node;
    }
}
