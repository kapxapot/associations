<?php

namespace Brightwood\Collections;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Links\StoryLink;
use Plasticode\Collections\Basic\TypedCollection;

class StoryLinkCollection extends TypedCollection
{
    protected string $class = StoryLink::class;

    /**
     * @return static
     */
    public function satisfying(?StoryData $data) : self
    {
        return $this->where(
            fn (StoryLink $l) => $l->satisfies($data)
        );
    }
}
