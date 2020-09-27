<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Collections\MessageCollection;
use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\StoryNode;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

abstract class Story
{
    public const RESTART_ACTION = '♻ Начать заново';

    private int $id;
    private string $name;
    private bool $published;

    private StoryNodeCollection $nodes;
    private ?StoryNode $startNode = null;

    private ?string $messagePrefix = null;

    public function __construct(
        int $id,
        string $name,
        bool $published = false
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->published = $published;

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

    public function isPublished() : bool
    {
        return $this->published;
    }

    public function nodes() : StoryNodeCollection
    {
        return $this->nodes;
    }

    public function startNode() : ?StoryNode
    {
        return $this->startNode;
    }

    abstract public function makeData(
        TelegramUser $tgUser,
        ?array $data = null
    ) : StoryData;

    public function executeCommand(string $command) : MessageCollection
    {
        return MessageCollection::empty();
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

    public function getNode(?int $id) : ?StoryNode
    {
        if (is_null($id)) {
            return null;
        }

        return $this->nodes->first(
            fn (StoryNode $n) => $n->id() == $id
        );
    }

    /**
     * Renders the start node with a fresh data.
     */
    public function start(TelegramUser $tgUser) : StoryMessageSequence
    {
        $node = $this->startNode();
        $data = $this->makeData($tgUser);

        return $this->renderNode($node, $data);
    }

    /**
     * Gets node's message (auto moving through nodes if possible).
     */
    public function renderNode(
        StoryNode $node,
        StoryData $data
    ) : StoryMessageSequence
    {
        $sequence = $node
            ->getMessages($data)
            ->prependPrefix($this->messagePrefix);

        return $this->checkForFinish($sequence);
    }

    /**
     * Checks if the node is a finish node (= no actions)
     * and adds restart action in that case.
     */
    public function checkForFinish(StoryMessageSequence $sequence) : StoryMessageSequence
    {
        $resultNode = $this->getNode($sequence->nodeId());

        return $resultNode && $resultNode->isFinish($sequence->data())
            ? $sequence->withActions(self::RESTART_ACTION)
            : $sequence;
    }

    /**
     * Attempts to go to the next node + renders it.
     * 
     * @throws InvalidConfigurationException
     */
    public function go(
        TelegramUser $tgUser,
        StoryNode $node,
        string $text,
        StoryData $data
    ) : ?StoryMessageSequence
    {
        if ($node->isFinish($data)) {
            return (self::RESTART_ACTION === $text)
                ? $this->start($tgUser)
                : null;
        }

        if ($node instanceof FunctionNode) {
            return $this->renderNode($node, $data);
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
