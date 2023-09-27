<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Webmozart\Assert\Assert;

class SkipNode extends AbstractMutatorNode
{
    private int $nextNodeId;

    public function __construct(int $id, array $text, int $nextNodeId)
    {
        parent::__construct($id, $text);
        
        $this->nextNodeId = $nextNodeId;
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $nextNode = $this->getNextNode();

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

        $nextNode = $this->getNextNode();

        Assert::notNull($nextNode);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getNextNode(): AbstractStoryNode
    {
        return $this->resolveNode($this->nextNodeId);
    }
}
