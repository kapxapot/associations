<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
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
        $this->stories = StoryCollection::make(
            [
                new WoodStory(1),
                new MysteryStory(2)
            ]
        );
    }

    public function get(?int $id) : ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->id() == $id
        );
    }
}
