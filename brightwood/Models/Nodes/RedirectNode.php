<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Collections\RedirectLinkCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\RedirectLink;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class RedirectNode extends AbstractLinkedNode
{
    private RedirectLinkCollection $links;

    /**
     * @param string[] $text
     * @param (RedirectLink|array|int)[] $links [nodeId, weight] or just `nodeId`
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $id, array $text, array $links)
    {
        parent::__construct($id, $text);

        $this->links = $this->parseLinks($links);

        Assert::notEmpty($this->links);
    }

    /**
     * @param (RedirectLink|array|int)[] $links
     *
     * @throws InvalidArgumentException
     */
    private function parseLinks(array $links): RedirectLinkCollection
    {
        $result = RedirectLinkCollection::empty();

        foreach ($links as $link) {
            if ($link instanceof RedirectLink) {
                $result = $result->add($link);
                continue;
            }

            if (is_array($link)) {
                [$nodeId, $weight] = $link;

                $result = $result->add(
                    new RedirectLink($nodeId, $weight)
                );

                continue;
            }

            if (is_int($link)) {
                $result = $result->add(
                    new RedirectLink($link)
                );

                continue;
            }

            throw new InvalidArgumentException("Invalid redirect link format.");
        }

        return $result;
    }

    public function links(): RedirectLinkCollection
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
        $satisfyingLinks = $this->links->satisfying($data);

        if ($satisfyingLinks->isEmpty()) {
            return
                StoryMessageSequence::textStuck(
                    '[[Redirect node {node_id} doesn\'t have available links.]]'
                )
                ->withVar('node_id', $this->id);
        }

        $link = $satisfyingLinks->choose();

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
     * @throws InvalidArgumentException
     */
    public function checkIntegrity(): void
    {
        parent::checkIntegrity();

        /** @var RedirectLink */
        foreach ($this->links as $link) {
            Assert::greaterThan($link->weight(), 0);
        }
    }
}
