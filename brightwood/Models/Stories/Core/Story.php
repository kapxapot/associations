<?php

namespace Brightwood\Models\Stories\Core;

use App\Models\TelegramUser;
use App\Models\Traits\Created;
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
use Brightwood\Models\StoryVersion;
use InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Webmozart\Assert\Assert;

/**
 * @property integer $id
 * @property string|null $uuid
 * @method StoryVersion|null currentVersion()
 * @method static withCurrentVersion(StoryVersion|callable|null $currentVersion)
 */
class Story extends DbModel implements CommandProviderInterface, CreatedInterface
{
    use Created;

    const MAX_TITLE_LENGTH = 250;
    const MAX_DESCRIPTION_LENGTH = 1000;

    const RESTART_COMMAND = '♻ Начать заново';
    const STORY_SELECTION_COMMAND = '📚 Выбрать историю';

    protected ?string $title = null;
    protected ?string $description = null;

    protected StoryNodeCollection $nodes;
    protected ?AbstractStoryNode $startNode = null;

    protected ?string $prefixMessage = null;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->nodes = StoryNodeCollection::empty();
    }

    public function title(): string
    {
        return $this->title ?? 'Untitled';
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function nodes(): StoryNodeCollection
    {
        return $this->nodes;
    }

    public function startNode(): ?AbstractStoryNode
    {
        return $this->startNode;
    }

    public function makeData(?array $data = null): StoryData
    {
        // overload this

        return new StoryData($data);
    }

    /**
     * Override this to handle story-specific commands.
     */
    public function executeCommand(string $command): StoryMessageSequence
    {
        return StoryMessageSequence::empty();
    }

    /**
     * Build the story and checks it for integrity.
     *
     * @throws InvalidArgumentException
     */
    protected function prepare(): void
    {
        $this->build();
        $this->checkIntegrity();
    }

    protected function build(): void
    {
        // overload this
    }

    /**
     * @return $this
     */
    public function setStartNode(AbstractStoryNode $node): self
    {
        Assert::null(
            $this->startNode,
            'Start node is already set.'
        );

        if (!$this->nodes->contains($node)) {
            $this->addNode($node);
        }

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
        $msg = new TextMessage(
            $this->toCommand()
        );

        if ($this->description()) {
            $msg->appendLines($this->description());
        };

        return $msg;
    }

    // CommandProviderInterface

    public function toCommand(): Command
    {
        return new Command(
            'story_' . $this->getId(),
            $this->title()
        );
    }
}
