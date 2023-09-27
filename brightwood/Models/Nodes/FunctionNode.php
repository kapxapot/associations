<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessageSequence;

class FunctionNode extends AbstractStoryNode
{
    /** @var callable */
    protected $actionFunc;

    /** @var callable */
    protected $finishFunc;

    public function __construct(
        int $id,
        callable $actionFunc,
        ?callable $finishFunc = null
    )
    {
        parent::__construct($id);

        $this->actionFunc = $actionFunc;
        $this->finishFunc = $finishFunc;
    }

    public function isFinish(?StoryData $data): bool
    {
        return $this->finishFunc
            ? ($this->finishFunc)($data)
            : parent::isFinish($data);
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        /** @var StoryMessageSequence */
        $sequence = ($this->actionFunc)($tgUser, $data, $input);

        // function sequence can have no actions and return the next node id
        // in this case the next node needs to be resolved
        if (!$sequence->hasActions() && $sequence->nodeId() !== $this->id) {
            $nextNode = $this->resolveNode($sequence->nodeId());

            $sequence = $sequence->merge(
                $nextNode->getMessages($tgUser, $data)
            );
        }

        return $sequence;
    }
}
