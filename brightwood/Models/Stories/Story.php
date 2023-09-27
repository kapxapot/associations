<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Command;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\CommandProviderInterface;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\AbstractStoryNode;
use InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Webmozart\Assert\Assert;

abstract class Story implements CommandProviderInterface
{
    public const RESTART_COMMAND = '♻ Начать заново';
    public const STORY_SELECTION_COMMAND = '📚 Выбрать историю';

    private int $id;
    private string $name;
    private string $description;

    private bool $published;

    private StoryNodeCollection $nodes;
    private ?AbstractStoryNode $startNode = null;

    private ?string $prefixMessage = null;

    public function __construct(
        int $id,
        string $name,
        string $description,
        bool $published = false
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;

        $this->published = $published;

        $this->nodes = StoryNodeCollection::empty();

        $this->build();
        $this->checkIntegrity();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function nodes(): StoryNodeCollection
    {
        return $this->nodes;
    }

    public function startNode(): ?AbstractStoryNode
    {
        return $this->startNode;
    }

    abstract public function makeData(?array $data = null): StoryData;

    /**
     * Override this.
     */
    public function executeCommand(string $command): StoryMessageSequence
    {
        return StoryMessageSequence::empty();
    }

    abstract protected function build(): void;

    /**
     * @return $this
     */
    public function setStartNode(AbstractStoryNode $node): self
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
    public function setPrefixMessage(string $msg): self
    {
        $this->prefixMessage = $msg;

        return $this;
    }

    /**
     * @return $this
     */
    public function addNode(AbstractStoryNode $node): self
    {
        $this->nodes = $this->nodes->add(
            $node->withStory($this)
        );

        return $this;
    }

    public function getNode(?int $id): ?AbstractStoryNode
    {
        if ($id === null) {
            return null;
        }

        return $this->nodes->first(
            fn (AbstractStoryNode $n) => $n->id() === $id
        );
    }

    /**
     * Renders the start node with a fresh data.
     */
    public function start(TelegramUser $tgUser): StoryMessageSequence
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
        AbstractStoryNode $node,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $sequence = $node
            ->getMessages($tgUser, $data, $input)
            ->prependMessage($this->prefixMessage);

        return $this->checkForFinish($sequence);
    }

    /**
     * Checks if the node is a finish node (= no actions)
     * and marks the sequence as finalized in that case.
     */
    public function checkForFinish(StoryMessageSequence $sequence): StoryMessageSequence
    {
        $resultNode = $this->getNode($sequence->nodeId());

        return $sequence->finalize(
            $resultNode && $resultNode->isFinish($sequence->data())
        );
    }

    /**
     * Attempts to go to the next node + renders it.
     *
     * Empty result sequence means here that story failed to move further due to some
     * reasons, e.g., incorrect input.
     *
     * @throws InvalidConfigurationException
     */
    public function go(
        TelegramUser $tgUser,
        AbstractStoryNode $node,
        StoryData $data,
        string $input
    ): ?StoryMessageSequence
    {
        if ($node->isFinish($data)) {
            return ($input === self::RESTART_COMMAND)
                ? $this->start($tgUser)
                : null;
        }

        if ($node instanceof FunctionNode) {
            return $this->renderNode($tgUser, $node, $data, $input);
        }

        if ($node instanceof ActionNode) {
            return $this->renderActionNode($tgUser, $node, $data, $input);
        }

        throw new InvalidConfigurationException(
            sprintf(
                'Incorrect node type: %s.',
                get_class($node)
            )
        );
    }

    // todo: move this inside of the action node somehow
    private function renderActionNode(
        TelegramUser $tgUser,
        ActionNode $node,
        StoryData $data,
        string $input
    ): ?StoryMessageSequence
    {
        $link = $node
            ->links()
            ->satisfying($data)
            ->first(
                fn (ActionLink $al) => $al->action() === $input
            );

        if (!$link) {
            return null;
        }

        $nextNode = $this->getNode($link->nodeId());

        Assert::notNull($nextNode);

        return $this->renderNode(
            $tgUser,
            $nextNode,
            $link->mutate($data)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkIntegrity(): void
    {
        Assert::notNull($this->startNode);
        Assert::notEmpty($this->nodes);

        foreach ($this->nodes as $node) {
            $node->checkIntegrity();
        }
    }

    public function toInfo(): TextMessage
    {
        return new TextMessage(
            $this->toCommand(),
            $this->description
        );
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
