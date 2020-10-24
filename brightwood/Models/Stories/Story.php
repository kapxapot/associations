<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Command;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\CommandProviderInterface;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\StoryNode;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

abstract class Story implements CommandProviderInterface
{
    public const RESTART_COMMAND = '♻ Начать заново';
    public const STORY_SELECTION_COMMAND = '📚 Выбрать историю';

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

    abstract public function makeData(?array $data = null) : StoryData;

    /**
     * Override this.
     */
    public function executeCommand(string $command) : StoryMessageSequence
    {
        return StoryMessageSequence::empty();
    }

    abstract protected function build() : void;

    /**
     * @return $this
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
     * @return $this
     */
    public function setMessagePrefix(string $msg) : self
    {
        $this->messagePrefix = $msg;

        return $this;
    }

    /**
     * @return $this
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
        $data = $this->makeData();

        return $this->renderNode($tgUser, $node, $data);
    }

    /**
     * Gets node's message (auto moving through nodes if possible).
     */
    public function renderNode(
        TelegramUser $tgUser,
        StoryNode $node,
        StoryData $data
    ) : StoryMessageSequence
    {
        $sequence = $node
            ->getMessages($tgUser, $data)
            ->prependPrefix($this->messagePrefix);

        return $this->checkForFinish($sequence);
    }

    /**
     * Checks if the node is a finish node (= no actions)
     * and marks the sequence as finalized in that case.
     */
    public function checkForFinish(StoryMessageSequence $sequence) : StoryMessageSequence
    {
        $resultNode = $this->getNode($sequence->nodeId());

        return $sequence->finalize(
            $resultNode && $resultNode->isFinish($sequence->data())
        );
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
            return (self::RESTART_COMMAND === $text)
                ? $this->start($tgUser)
                : null;
        }

        if ($node instanceof FunctionNode) {
            return $this->renderNode($tgUser, $node, $data);
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

            return $this->renderNode($tgUser, $nextNode, $data);
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

    // CommandProviderInterface

    public function toCommand(): Command
    {
        return new Command(
            'story_' . $this->id(),
            $this->name()
        );
    }
}
