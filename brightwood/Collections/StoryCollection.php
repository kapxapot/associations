<?php

namespace Brightwood\Collections;

use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\Story;
use Plasticode\Collections\Generic\TypedCollection;

class StoryCollection extends TypedCollection
{
    protected string $class = Story::class;

    public function toCommands(): CommandCollection
    {
        return CommandCollection::from(
            $this->map(
                fn (Story $s) => $s->toCommand()
            )
        );
    }

    public function toInfo(): StoryMessageSequence
    {
        return StoryMessageSequence::make(
            ...$this->map(
                fn (Story $s) => $s->toInfo()
            )
        );
    }
}
