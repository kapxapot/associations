<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Collections\RedirectLinkCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\RedirectLink;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Webmozart\Assert\Assert;

class RedirectNode extends LinkedNode
{
    private RedirectLinkCollection $links;

    /**
     * @param string[] $text
     * @param RedirectLink[] $links
     */
    public function __construct(
        int $id,
        array $text,
        array $links
    )
    {
        parent::__construct($id, $text);

        Assert::notEmpty($links);

        $this->links = RedirectLinkCollection::make($links);
    }

    public function links() : RedirectLinkCollection
    {
        return $this->links;
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $text = null
    ) : StoryMessageSequence
    {
        $data = $this->mutate($data);
        $link = $this->links->satisfying($data)->choose();

        Assert::notNull($link);

        $nextNode = $this->resolveNode($link->nodeId());
        $data = $link->mutate($data);

        return StoryMessageSequence::mash(
            new StoryMessage(
                $this->id, $this->text, null, $data
            ),
            $nextNode->getMessages($tgUser, $data)
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
