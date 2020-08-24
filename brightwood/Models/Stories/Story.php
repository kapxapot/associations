<?php

namespace Brightwood\Models\Stories;

use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Nodes\StoryNode;
use Webmozart\Assert\Assert;

abstract class Story
{
    private int $id;
    private StoryNodeCollection $nodes;
    private ?StoryNode $startNode = null;

    public function __construct(
        int $id
    )
    {
        $this->id = $id;
        $this->nodes = StoryNodeCollection::empty();

        $this->build();
        $this->checkIntegrity();
    }

    public function id() : int
    {
        return $this->id;
    }

    public function nodes() : StoryNodeCollection
    {
        return $this->nodes;
    }

    abstract protected function build() : void;

    /**
     * @return static
     */
    public function setStartNode(StoryNode $node) : self
    {
        Assert::null(
            $this->startNode,
            'Start node is already set.'
        );

        $this->addNode($node);
        $this->startNode = $node;

        return $this;
    }

    /**
     * @return static
     */
    public function addNode(StoryNode $node) : self
    {
        $this->nodes = $this->nodes->add(
            $node->withStory($this)
        );

        return $this;
    }

    public function getNode(int $id) : ?StoryNode
    {
        return $this->nodes->first(
            fn (StoryNode $n) => $n->id() == $id
        );
    }

    public function startNode() : ?StoryNode
    {
        return $this->startNode;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
        Assert::notNull($this->startNode);
        Assert::notEmpty($this->nodes);

        foreach ($this->nodes as $node) {
            $node->checkIntegrity();
        }
    }
}
