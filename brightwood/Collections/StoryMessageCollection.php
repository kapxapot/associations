<?php

namespace Brightwood\Collections;

use Brightwood\Models\Messages\StoryMessage;

class StoryMessageCollection extends MessageCollection
{
    protected string $class = StoryMessage::class;
}
