<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Stories\Story;
use Webmozart\Assert\Assert;

abstract class StoryNode
{
    protected ?Story $story = null;
    protected int $id;

    /** @var string[] */
    protected array $text;

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

    abstract public function isFinish() : bool;

    public function getMessage() : StoryMessage
    {
        return new StoryMessage(
            $this->id,
            $this->text
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
