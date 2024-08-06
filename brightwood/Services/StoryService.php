<?php

namespace Brightwood\Services;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StaticStoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;

class StoryService
{
    private StoryRepositoryInterface $storyRepository;

    private array $cache = [];

    public function __construct(
        StaticStoryRepositoryInterface $staticStoryRepository,
        StoryRepositoryInterface $storyRepository
    )
    {
        $this->storyRepository = $storyRepository;

        $staticStoryRepository
            ->getAll()
            ->apply(
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
            ? $this->getCachedOrAddJsonStory($story)
            : null;
    }

    public function getStories(): StoryCollection
    {
        return $this->toRichStories(
            $this->storyRepository->getAll()
        );
    }

    public function getStoriesEditableBy(TelegramUser $tgUser): StoryCollection
    {
        return $this->toRichStories(
            $this->storyRepository->getAllEditableBy($tgUser)
        );
    }

    public function getDefaultStoryId(): int
    {
        return WoodStory::ID;
    }

    public function makeStoryFromJson(string $json): JsonStory
    {
        $story = new Story();
        $story->withCurrentVersion(new StoryVersion(['json_data' => $json]));

        return new JsonStory($story);
    }

    /**
     * Converts every story into a static story or a json story.
     */
    private function toRichStories(StoryCollection $stories): StoryCollection
    {
        return StoryCollection::from(
            $stories->map(
                fn (Story $s) => $this->getCachedOrAddJsonStory($s)
            )
        );
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

    private function getCachedOrAddJsonStory(Story $story): Story
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
