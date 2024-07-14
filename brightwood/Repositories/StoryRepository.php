<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\JsonFileStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use EightsStoryFactory;

/**
 * Stub repository for now.
 */
class StoryRepository implements StoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct(EightsStoryFactory $eightsFactory)
    {
        $this->stories = StoryCollection::collect(
            new WoodStory(),
            new JsonFileStory(2, 'mystery.json'),
            ($eightsFactory)(),
            new JsonFileStory(6, '359e097f-5620-477b-930d-48496393f747.json')
        );
    }

    public function get(?int $id): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->id() === $id
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }
}
