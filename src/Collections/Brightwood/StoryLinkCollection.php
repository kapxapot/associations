<?php

namespace App\Collections\Brightwood;

use App\Models\Brightwood\Links\StoryLink;
use Plasticode\Collections\Basic\TypedCollection;

class StoryLinkCollection extends TypedCollection
{
    protected string $class = StoryLink::class;
}
