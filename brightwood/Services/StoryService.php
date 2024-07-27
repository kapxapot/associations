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

    private array $cache = [];

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

        $this->fixedStories->apply(
            fn (Story $s) => $this->addToCache($s)
        );
    }

    public function getStory(int $id): ?Story
    {
        return $this->getFromCache($id)
            ?? $this->addToCache(
                $this->getJsonStory($id)
            );
    }

    public function getStoryByUuid(string $uuid): ?Story
    {
        $story = $this->storyRepository->getByUuid($uuid);

        return $story
            ? $this->getOrAddJsonStory($story)
            : null;
    }

    public function getStories(): StoryCollection
    {
        return StoryCollection::from(
            $this
                ->storyRepository
                ->getAll()
                ->asc('id')
                ->map(
                    fn (Story $s) => $this->getOrAddJsonStory($s)
                )
        );
    }

    public function getDefaultStoryId(): int
    {
        return WoodStory::ID;
    }

    private function addToCache(Story $story): Story
    {
        $this->cache[$story->getId()] = $story;
        return $story;
    }

    private function getFromCache(int $id): ?Story
    {
        return $this->cache[$id] ?? null;
    }

    private function getJsonStory(int $id): ?JsonStory
    {
        $story = $this->storyRepository->get($id);

        return $story
            ? new JsonStory($story)
            : null;
    }

    private function getOrAddJsonStory(Story $story): Story
    {
        $cached = $this->getFromCache($story->getId());

        if ($cached) {
            return $cached;
        }

        return $this->addToCache(
            new JsonStory($story)
        );
    }
}
