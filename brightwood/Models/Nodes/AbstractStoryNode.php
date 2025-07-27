<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\Core\Story;
use InvalidArgumentException;
use Plasticode\Models\Interfaces\EquatableInterface;
use Webmozart\Assert\Assert;

abstract class AbstractStoryNode implements EquatableInterface
{
    protected int $id;
    protected ?Story $story = null;
    protected bool $isFinish = false;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function withStory(Story $story): self
    {
        $this->story = $story;

        return $this;
    }

    public function isFinish(?StoryData $data): bool
    {
        return false;
    }

    /**
     * @param string|null $input Arbitrary user input. Not an action! Usually empty.
     */
    abstract public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence;

    /**
     * @throws InvalidArgumentException
     */
    public function checkIntegrity(): void
    {
    }

    /**
     * Tries to get a node by id.
     *
     * Throws an {@see InvalidArgumentException} if the node is not found.
     *
     * @throws InvalidArgumentException
     */
    protected function resolveNode(int $id): self
    {
        $node = $this->story->getNode($id);

        Assert::notNull($node, "Node {$id} not found.");

        return $node;
    }

    public function equals(?EquatableInterface $obj): bool
    {
        return $obj instanceof self
            && $obj->id() === $this->id;
    }
}
