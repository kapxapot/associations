<?php

namespace Brightwood\Services;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Models\StoryCandidate;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StaticStoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryCandidateRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Util\Date;

class StoryService
{
    private StoryRepositoryInterface $storyRepository;
    private StoryCandidateRepositoryInterface $storyCandidateRepository;
    private StoryVersionRepositoryInterface $storyVersionRepository;

    private array $cache = [];

    public function __construct(
        StaticStoryRepositoryInterface $staticStoryRepository,
        StoryRepositoryInterface $storyRepository,
        StoryCandidateRepositoryInterface $storyCandidateRepository,
        StoryVersionRepositoryInterface $storyVersionRepository
    )
    {
        $this->storyRepository = $storyRepository;
        $this->storyCandidateRepository = $storyCandidateRepository;
        $this->storyVersionRepository = $storyVersionRepository;

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

    public function getStoryCandidate(TelegramUser $tgUser): ?StoryCandidate
    {
        $user = $tgUser->user();
        return $this->storyCandidateRepository->getByCreator($user);
    }

    public function saveStoryCandidate(TelegramUser $tgUser, string $json): StoryCandidate
    {
        $user = $tgUser->user();
        $candidate = $this->storyCandidateRepository->getByCreator($user);

        if (!$candidate) {
            $candidate = StoryCandidate::create([
                'created_by' => $user->getId()
            ]);
        }

        $candidate->jsonData = $json;

        return $this->storyCandidateRepository->save($candidate);
    }

    public function createStoryFromCandidate(string $uuid, StoryCandidate $candidate): JsonStory
    {
        // create story entity
        $story = $this->storyRepository->store([
            'uuid' => $uuid,
            'created_by' => $candidate->createdBy
        ]);

        // create story version entity
        $storyVersion = $this->storyVersionRepository->store([
            'story_id' => $story->getId(),

        ]);

        // remove candidate

        // return json story
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
