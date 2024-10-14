<?php

namespace Brightwood\Models\Stories\Core;

use App\Models\TelegramUser;
use App\Models\Traits\Created;
use App\Models\User;
use Brightwood\Collections\StoryNodeCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\AbstractStoryNode;
use Brightwood\Models\StoryStatus;
use Brightwood\Models\StoryVersion;
use Brightwood\Models\ValidationResult;
use Exception;
use InvalidArgumentException;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

/**
 * @property string|null $deletedAt
 * @property integer|null $deletedBy
 * @property integer $id
 * @property string|null $langCode
 * @property integer|null $sourceStoryId
 * @property string|null $uuid
 * @method StoryVersion|null currentVersion()
 * @method static withCurrentVersion(StoryVersion|callable|null $currentVersion)
 * @method Story|null sourceStory()
 * @method static withSourceStory(Story|callable|null $sourceStory)
 * @method User|null deleter()
 * @method static withDeleter(User|callable|null $deleter)
 */
class Story extends DbModel implements CreatedInterface
{
    use Created;

    const MAX_TITLE_LENGTH = 250;
    const MAX_DESCRIPTION_LENGTH = 1000;
    const MAX_COVER_LENGTH = 500;
    const MAX_LANG_CODE_LENGTH = 10;

    protected string $title = 'Untitled';
    protected ?string $description = null;
    protected ?string $cover = null;

    protected StoryNodeCollection $nodes;
    protected ?AbstractStoryNode $startNode = null;

    protected ?string $prefixMessage = null;

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->nodes = StoryNodeCollection::empty();
    }

    public function title(): string
    {
        return Strings::trunc(
            $this->title,
            self::MAX_TITLE_LENGTH
        );
    }

    public function description(): ?string
    {
        if (!$this->description) {
            return null;
        }

        return Strings::trunc(
            $this->description,
            self::MAX_DESCRIPTION_LENGTH
        );
    }

    public function cover(): ?string
    {
        if (!$this->cover) {
            return null;
        }

        return Strings::trunc(
            $this->cover,
            self::MAX_COVER_LENGTH
        );
    }

    public function languageCode(): ?string
    {
        return $this->langCode;
    }

    public function hasUuid(): bool
    {
        return strlen($this->uuid) > 0;
    }

    public function isEditable(): bool
    {
        return false;
    }

    public function isDeletable(): bool
    {
        return false;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function nodes(): StoryNodeCollection
    {
        return $this->nodes;
    }

    public function startNode(): ?AbstractStoryNode
    {
        return $this->startNode;
    }

    public function newData(): StoryData
    {
        return new StoryData();
    }

    public function loadData(array $data): StoryData
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
        $data = $this->newData();

        return $this->renderNode($tgUser, $node, $data);
    }

    public function renderStatus(StoryStatus $status): StoryMessageSequence
    {
        $node = $this->getNode($status->stepId);
        $data = $this->loadData($status->data());

        return $this->renderNode(
            $status->telegramUser(),
            $node,
            $data
        );
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
     * Status must be validated before calling this method.
     */
    public function isFinish(StoryStatus $status): bool
    {
        $node = $this->getNode($status->stepId);
        $data = $this->loadData($status->data());

        return $node->isFinish($data);
    }

    /**
     * @throws Exception
     */
    public function validateStatus(StoryStatus $status): ValidationResult
    {
        if ($this->isDeleted()) {
            return ValidationResult::error(
                StoryMessageSequence::text(
                    '[[The story {{story_title}} is deleted.]]'
                )
                ->withVar('story_title', $this->title())
            );
        }

        $nodeId = $status->stepId;
        $node = $this->getNode($nodeId);

        if (!$node) {
            return ValidationResult::error(
                $this->nodeNotFound($nodeId)
            );
        }

        try {
            $this->loadData($status->data());
        } catch (InvalidArgumentException $ex) {
            return ValidationResult::error(
                StoryMessageSequence::text(
                    '[[Failed to restore the story state.]]'
                )
            );
        }

        return ValidationResult::ok();
    }

    /**
     * Attempts to continue the story.
     *
     * Status must be validated before calling this method.
     *
     * Empty result sequence means here that story failed to move further due to some
     * reasons, e.g., incorrect input.
     */
    public function continue(
        TelegramUser $tgUser,
        StoryStatus $status,
        string $input
    ): StoryMessageSequence
    {
        $node = $this->getNode($status->stepId);
        $data = $this->loadData($status->data());

        if ($node instanceof FunctionNode) {
            return $this->renderNode($tgUser, $node, $data, $input);
        }

        if ($node instanceof ActionNode) {
            return $this->renderActionNode($tgUser, $node, $data, $input);
        }

        return StoryMessageSequence::textStuck(
            '[[Incorrect node type]]: ' . get_class($node) . '.'
        );
    }

    /**
     * todo: move this inside of the action node somehow
     */
    private function renderActionNode(
        TelegramUser $tgUser,
        ActionNode $node,
        StoryData $data,
        string $input
    ): StoryMessageSequence
    {
        $satisfyingLinks = $node->links()->satisfying($data);

        if ($satisfyingLinks->isEmpty()) {
            return
                StoryMessageSequence::textStuck(
                    '[[Action node {{node_id}} doesn\'t have available links.]]'
                )
                ->withVar('node_id', $this->id);
        }

        $link = $satisfyingLinks->first(
            fn (ActionLink $al) => $al->action() === $input
        );

        if (!$link) {
            return StoryMessageSequence::empty();
        }

        $nextNodeId = $link->nodeId();
        $nextNode = $this->getNode($nextNodeId);

        if (!$nextNode) {
            return $this->nodeNotFound($nextNodeId);
        }

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

        /** @var AbstractStoryNode */
        foreach ($this->nodes as $node) {
            $node->checkIntegrity();
        }
    }

    private function nodeNotFound(int $nodeId): StoryMessageSequence
    {
        return
            StoryMessageSequence::textStuck(
                '[[Node {{node_id}} not found.]]'
            )
            ->withVar('node_id', $nodeId);
    }
}
