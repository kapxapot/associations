<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\MysteryStory;
use Brightwood\Models\Stories\Story;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;

/**
 * Stub repository for now.
 */
class StoryRepository implements StoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct()
    {
        $this->stories = StoryCollection::collect(
            new WoodStory(1),
            new MysteryStory(2),
            new EightsStory(3)
        );
    }

    public function get(?int $id) : ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->id() == $id
        );
    }

    public function getAllPublished(): StoryCollection
    {
        return $this->stories->where(
            fn (Story $s) => $s->isPublished()
        );
    }
}
