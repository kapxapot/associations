<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessageSequence;
use Plasticode\Models\TelegramUser;

class FunctionNode extends StoryNode
{
    /** @var callable */
    protected $function;

    public function __construct(
        int $id,
        callable $function
    )
    {
        parent::__construct($id);

        $this->function = $function;
    }

    public function isFinish(?StoryData $data): bool
    {
        // todo: allow to redefine this
        return false;
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $text = null
    ): StoryMessageSequence
    {
        /** @var StoryMessageSequence */
        $sequence = ($this->function)($tgUser, $data, $text);

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
