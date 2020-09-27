<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessageSequence;

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

    public function isFinish(?StoryData $data) : bool
    {
        // todo: allow to redefine this
        return false;
    }

    public function getMessages(StoryData $data) : StoryMessageSequence
    {
        return ($this->function)($data);
    }
}
