<?php

namespace Brightwood\Models\Stories;

use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\StoryNode;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

abstract class Story
{
    private const RESTART_ACTION = 'Начать заново';

    private int $id;
    private string $name;
    private StoryNodeCollection $nodes;
    private ?StoryNode $startNode = null;

    private ?string $messagePrefix = null;

    public function __construct(
        int $id,
        string $name
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->nodes = StoryNodeCollection::empty();

        $this->build();
        $this->checkIntegrity();
    }

    public function id() : int
    {
        return $this->id;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function nodes() : StoryNodeCollection
    {
        return $this->nodes;
    }

    public function startNode() : ?StoryNode
    {
        return $this->startNode;
    }

    abstract public function makeData(?array $data = null) : StoryData;

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
    public function setMessagePrefix(string $msg) : self
    {
        $this->messagePrefix = $msg;

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

    /**
     * Renders the start node with a fresh data.
     */
    public function start() : StoryMessage
    {
        $node = $this->startNode();
        $data = $this->makeData();

        return $this->renderNode($node, $data);
    }

    /**
     * Gets node's message (auto moving through nodes if possible).
     */
    public function renderNode(StoryNode $node, ?StoryData $data = null) : StoryMessage
    {
        $message = $node->getMessage($data);
        $message = $this->checkForFinish($message);

        return $this->addPrefix($message);
    }

    /**
     * Checks if the node is a finish node (= no actions)
     * and adds restart action in that case.
     */
    private function checkForFinish(StoryMessage $message) : StoryMessage
    {
        $resultNode = $this->getNode($message->nodeId());

        return $resultNode && $resultNode->isFinish()
            ? $message->withActions(self::RESTART_ACTION)
            : $message;
    }

    private function addPrefix(StoryMessage $message) : StoryMessage
    {
        if (strlen($this->messagePrefix) == 0) {
            return $message;
        }

        return $message->prependLines(
            $this->messagePrefix
        );
    }

    /**
     * Attempts to go to the next node + renders it.
     */
    public function go(StoryNode $node, string $text, ?StoryData $data) : ?StoryMessage
    {
        if ($node->isFinish()) {
            return (self::RESTART_ACTION === $text)
                ? $this->start()
                : null;
        }

        if (!($node instanceof ActionNode)) {
            throw new InvalidConfigurationException(
                'Incorrect node type: ' . get_class($node) . '.'
            );
        }

        /** @var ActionLink */
        foreach ($node->links()->satisfying($data) as $link) {
            if ($link->action() !== $text) {
                continue;
            }

            $nextNode = $this->getNode($link->nodeId());

            Assert::notNull($nextNode);

            $data = $link->mutate($data);

            return $this->renderNode($nextNode, $data);
        }

        return null;
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
