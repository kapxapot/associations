<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Collections\ActionLinkCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\ActionLink;
use Brightwood\Models\Messages\StoryMessage;
use Webmozart\Assert\Assert;

class ActionNode extends LinkedNode
{
    private ActionLinkCollection $links;

    /**
     * @param string[] $text
     * @param array<int, string> $links NodeId -> Text
     */
    public function __construct(
        int $id,
        array $text,
        array $links
    )
    {
        parent::__construct($id, $text);

        Assert::notEmpty($links);

        $this->links = ActionLinkCollection::make(
            array_map(
                fn (int $nodeId, string $text) => new ActionLink($nodeId, $text),
                array_keys($links),
                $links
            )
        );
    }

    public function links() : ActionLinkCollection
    {
        return $this->links;
    }

    public function getMessage(?StoryData $data = null) : StoryMessage
    {
        $message = parent::getMessage($data);
        $data = $message->data();

        return $message->withActions(
            ...$this->links->satisfying($data)->actions()
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
        parent::checkIntegrity();

        /** @var ActionLink */
        foreach ($this->links as $link) {
            Assert::stringNotEmpty($link->action());
        }
    }
}
