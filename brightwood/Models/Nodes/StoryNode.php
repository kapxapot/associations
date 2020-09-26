<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Stories\Story;
use Webmozart\Assert\Assert;

abstract class StoryNode
{
    protected ?Story $story = null;
    protected int $id;

    public function __construct(
        int $id
    )
    {
        $this->id = $id;
    }

    public function id() : int
    {
        return $this->id;
    }

    /**
     * @return static
     */
    public function withStory(Story $story) : self
    {
        $this->story = $story;
        return $this;
    }

    abstract public function isFinish() : bool;

    abstract public function getMessage(StoryData $data) : StoryMessage;

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
