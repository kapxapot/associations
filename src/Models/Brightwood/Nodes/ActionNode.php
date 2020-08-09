<?php

namespace App\Models\Brightwood\Nodes;

use App\Collections\Brightwood\ActionLinkCollection;
use App\Models\Brightwood\Links\ActionLink;
use App\Models\Brightwood\StoryMessage;
use Webmozart\Assert\Assert;

class ActionNode extends LinkedNode
{
    private ActionLinkCollection $links;

    public function __construct(
        int $id,
        string $text,
        ActionLink ...$links
    )
    {
        parent::__construct($id, $text);

        Assert::notEmpty($links);

        $this->links = ActionLinkCollection::make($links);
    }

    public function links() : ActionLinkCollection
    {
        return $this->links;
    }

    public function getMessage() : StoryMessage
    {
        return parent::getMessage()->withActions(
            ...$this->links->actions()
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
