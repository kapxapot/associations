<?php

namespace Brightwood\Collections;

use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Collections\Generic\DbModelCollection;

class StoryCollection extends DbModelCollection
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
