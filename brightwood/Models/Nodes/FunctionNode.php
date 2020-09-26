<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessage;

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

    public function isFinish() : bool
    {
        // todo: allow to redefine this
        return false;
    }

    public function getMessage(StoryData $data) : StoryMessage
    {
        return ($this->function)($data);
    }
}
