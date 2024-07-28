<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StaticStoryRepositoryInterface;

class StaticStoryRepository implements StaticStoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct(
        WoodStory $woodStory,
        EightsStory $eightsStory
    )
    {
        $this->stories = StoryCollection::collect(
            $woodStory,
            $eightsStory
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }
}
