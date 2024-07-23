<?php

namespace Brightwood\Services;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;

class StoryService
{
    private StoryRepositoryInterface $storyRepository;

    private StoryCollection $fixedStories;

    public function __construct(
        StoryRepositoryInterface $storyRepository,
        WoodStory $woodStory,
        EightsStory $eightsStory
    )
    {
        $this->storyRepository = $storyRepository;

        $this->fixedStories = StoryCollection::collect(
            $woodStory,
            $eightsStory
        );
    }

    public function getStory(?int $id): ?Story
    {
        // first, try to get a fixed story
        $story = $this->fixedStories->first(
            fn (Story $s) => $s->getId() == $id
        );

        if ($story) {
            return $story;
        }

        // second, get a story from the db and convert it into a `JsonStory`
        $story = $this->storyRepository->get($id);

        return $story
            ? new JsonStory($story)
            : null;
    }

    public function getStories(): StoryCollection
    {
        return $this->fixedStories->concat(
            $this->storyRepository->getAll()
        );
    }

    public function getDefaultStoryId(): int
    {
        return WoodStory::ID;
    }
}
