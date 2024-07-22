<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use EightsStoryFactory;
use WoodStoryFactory;

/**
 * Stub repository for now.
 */
class StoryRepository implements StoryRepositoryInterface
{
    private StoryCollection $fixedStories;

    public function __construct(
        WoodStoryFactory $woodStoryFactory,
        EightsStoryFactory $eightsFactory
    )
    {
        $this->fixedStories = StoryCollection::collect(
            ($woodStoryFactory)(),
            ($eightsFactory)(),
        );
    }

    public function get(?int $id): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->getId() === $id
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }
}
