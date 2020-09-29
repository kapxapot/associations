<?php

namespace Brightwood\Collections;

use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\StoryMessage;
use Plasticode\Collections\Basic\TypedCollection;

class MessageCollection extends TypedCollection
{
    protected string $class = MessageInterface::class;

    public function storyMessages() : StoryMessageCollection
    {
        return StoryMessageCollection::from(
            $this->where(
                fn (MessageInterface $m) => $m instanceof StoryMessage
            )
        );
    }
}
