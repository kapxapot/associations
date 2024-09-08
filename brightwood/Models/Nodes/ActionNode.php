<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Collections\ActionLinkCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class ActionNode extends AbstractLinkedNode
{
    private ActionLinkCollection $links;

    /**
     * @param string[] $text
     * @param (ActionLink|array)[] $links [nodeId, action]
     */
    public function __construct(int $id, array $text, array $links)
    {
        parent::__construct($id, $text);

        $this->links = $this->parseLinks($links);

        Assert::notEmpty($this->links);
    }

    /**
     * @param (ActionLink|array)[] $links
     *
     * @throws InvalidArgumentException
     */
    private function parseLinks(array $links): ActionLinkCollection
    {
        $result = ActionLinkCollection::empty();

        foreach ($links as $link) {
            if ($link instanceof ActionLink) {
                $result = $result->add($link);
                continue;
            }

            if (is_array($link)) {
                [$nodeId, $action] = $link;

                $result = $result->add(
                    new ActionLink($nodeId, $action)
                );

                continue;
            }

            throw new InvalidArgumentException("Invalid action link format.");
        }

        return $result;
    }

    public function links(): ActionLinkCollection
    {
        return $this->links;
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $data = $this->mutate($data);
        $actions = $this->links->satisfying($data)->actions();

        return new StoryMessageSequence(
            new StoryMessage(
                $this->id, $this->text, $actions, $data
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkIntegrity(): void
    {
        parent::checkIntegrity();

        /** @var ActionLink */
        foreach ($this->links as $link) {
            Assert::stringNotEmpty($link->action());
        }
    }
}
