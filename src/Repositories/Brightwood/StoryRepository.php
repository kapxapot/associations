<?php

namespace App\Repositories\Brightwood;

use App\Collections\Brightwood\StoryCollection;
use App\Models\Brightwood\Stories\DemoStory;
use App\Models\Brightwood\Stories\Story;
use App\Repositories\Brightwood\Interfaces\StoryRepositoryInterface;

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
                new DemoStory(1)
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
