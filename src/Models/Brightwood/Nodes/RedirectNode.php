<?php

namespace App\Models\Brightwood\Nodes;

use App\Collections\Brightwood\RedirectLinkCollection;
use App\Models\Brightwood\Links\RedirectLink;
use App\Models\Brightwood\StoryMessage;
use Webmozart\Assert\Assert;

class RedirectNode extends LinkedNode
{
    private RedirectLinkCollection $links;

    /**
     * @param array<int, float|null> $links
     */
    public function __construct(
        int $id,
        string $text,
        array $links
    )
    {
        parent::__construct($id, $text);

        Assert::notEmpty($links);

        $this->links = RedirectLinkCollection::make(
            array_map(
                fn (int $nodeId, ?float $weight) => new RedirectLink($nodeId, $weight),
                array_keys($links),
                $links
            )
        );
    }

    public function links() : RedirectLinkCollection
    {
        return $this->links;
    }

    public function getMessage() : StoryMessage
    {
        $nextLink = $this->links->choose();

        Assert::notNull($nextLink);

        $nextNode = $this->resolveNode($nextLink->nodeId());

        return parent::getMessage()->merge(
            $nextNode->getMessage()
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
        parent::checkIntegrity();

        /** @var RedirectLink */
        foreach ($this->links as $link) {
            Assert::greaterThan($link->weight(), 0);
        }
    }
}
