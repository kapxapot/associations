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

class StoryService
{
    private StoryRepositoryInterface $storyRepository;
    private StoryCandidateRepositoryInterface $storyCandidateRepository;
    private StoryVersionRepositoryInterface $storyVersionRepository;

    private TelegramUserService $telegramUserService;

    private array $cache = [];

    public function __construct(
        StaticStoryRepositoryInterface $staticStoryRepository,
        StoryRepositoryInterface $storyRepository,
        StoryCandidateRepositoryInterface $storyCandidateRepository,
        StoryVersionRepositoryInterface $storyVersionRepository,
        TelegramUserService $telegramUserService
    )
    {
        $this->storyRepository = $storyRepository;
        $this->storyCandidateRepository = $storyCandidateRepository;
        $this->storyVersionRepository = $storyVersionRepository;

        $this->telegramUserService = $telegramUserService;

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

    public function getStoriesPlayableBy(TelegramUser $tgUser): StoryCollection
    {
        return $this->toRichStories(
            $this->storyRepository->getAllPlayableBy($tgUser)
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

    public function saveStoryCandidate(TelegramUser $tgUser, array $jsonData): StoryCandidate
    {
        $user = $tgUser->user();
        $candidate = $this->storyCandidateRepository->getByCreator($user);

        if (!$candidate) {
            $candidate = StoryCandidate::create([
                'created_by' => $user->getId()
            ]);
        }

        $candidate->jsonData = json_encode($jsonData);
        $candidate->uuid = $jsonData['id'];

        return $this->storyCandidateRepository->save($candidate);
    }

    /**
     * Creates a story + a story version from a story candidate.
     * Then deletes the candidate.
     *
     * @param string|null $uuid If uuid is provided, a story with this uuid is created.
     */
    public function newStory(
        StoryCandidate $storyCandidate,
        ?string $uuid = null
    ): JsonStory
    {
        $story = $this->storyRepository->store([
            'uuid' => $uuid ?? $storyCandidate->uuid,
            'created_by' => $storyCandidate->createdBy
        ]);

        $jsonData = $storyCandidate->jsonData;

        if ($uuid) {
            $jsonData['id'] = $uuid;
        }

        $this->storyVersionRepository->store([
            'story_id' => $story->getId(),
            'json_data' => $jsonData,
            'created_by' => $storyCandidate->createdBy
        ]);

        $this->deleteStoryCandidate($storyCandidate);

        return new JsonStory($story);
    }

    /**
     * Applies story candidate to the story as a new story version.
     */
    public function updateStory(Story $story, StoryCandidate $storyCandidate): JsonStory
    {
        $currentVersion = $story->currentVersion();

        $newVersion = $this->storyVersionRepository->store([
            'story_id' => $story->getId(),
            'prev_version_id' => $currentVersion ? $currentVersion->getId() : null,
            'json_data' => $storyCandidate->jsonData,
            'created_by' => $storyCandidate->createdBy
        ]);

        $this->deleteStoryCandidate($storyCandidate);

        $story->withCurrentVersion($newVersion);

        return new JsonStory($story);
    }

    public function deleteStoryCandidateFor(TelegramUser $tgUser): bool
    {
        $candidate = $this->getStoryCandidateFor($tgUser);

        return $candidate
            ? $this->deleteStoryCandidate($candidate)
            : false;
    }

    public function getStoryCandidateFor(TelegramUser $tgUser): ?StoryCandidate
    {
        $user = $tgUser->user();
        return $this->storyCandidateRepository->getByCreator($user);
    }

    /**
     * Admin can play any story.
     */
    public function isStoryPlayableBy(Story $story, TelegramUser $tgUser): bool
    {
        $creator = $story->creator();

        if (!$creator) {
            return true;
        }

        $isAdmin = $this->telegramUserService->isAdmin($tgUser);

        if ($isAdmin) {
            return true;
        }

        $user = $tgUser->user();

        return $creator->equals($user);
    }

    /**
     * Admin can edit any story.
     */
    public function isStoryEditableBy(Story $story, TelegramUser $tgUser): bool
    {
        if (!$story->hasUuid()) {
            return false;
        }

        $creator = $story->creator();

        if ($creator) {
            $user = $tgUser->user();
            return $creator->equals($user);
        }

        return $this->telegramUserService->isAdmin($tgUser);
    }

    public function applyVersion(Story $story, ?StoryVersion $storyVersion): Story
    {
        // if there is no version or the story is already on that version, do nothing
        if (!$storyVersion || $storyVersion->equals($story->currentVersion())) {
            return $story;
        }

        $storyCopy = Story::create($story->toArray());
        $storyCopy->withCurrentVersion($storyVersion);

        return new JsonStory($storyCopy);
    }

    private function deleteStoryCandidate(StoryCandidate $candidate): bool
    {
        return $this->storyCandidateRepository->delete($candidate);
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
