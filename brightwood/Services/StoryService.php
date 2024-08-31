<?php

namespace Brightwood\Services;

use App\Models\Interfaces\UserInterface;
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
        $cachedStory = $this->getFromCache($id);

        if ($cachedStory) {
            return $cachedStory;
        }

        $jsonStory = $this->getJsonStory($id);

        return $jsonStory
            ? $this->addToCache($jsonStory)
            : null;
    }

    public function getStoryByUuid(string $uuid): ?Story
    {
        $story = $this->storyRepository->getByUuid($uuid);

        return $story
            ? $this->getCachedOrAddJsonStory($story)
            : null;
    }

    public function getStoriesPlayableBy(TelegramUser $tgUser, ?string $langCode = null): StoryCollection
    {
        $playableStories = $this
            ->storyRepository
            ->getAllByLanguage($langCode)
            ->where(
                fn (Story $s) => $this->isStoryPlayableBy($s, $tgUser)
            );

        return $this->toRichStories($playableStories);
    }

    public function getStoriesEditableBy(TelegramUser $tgUser): StoryCollection
    {
        $editableStories = $this
            ->storyRepository
            ->getAll()
            ->where(
                fn (Story $s) => $this->isStoryEditableBy($s, $tgUser)
            );

        return $this->toRichStories($editableStories);
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
     * Story is playable by user if any:
     * - the story is public
     * - the user is an admin
     * - the user is the creator
     */
    public function isStoryPlayableBy(Story $story, UserInterface $user): bool
    {
        return $this->isStoryPublic($story)
            || $this->isAdminOrStoryCreator($story, $user);
    }

    /**
     * Story is editable by user if both:
     * - the story is editable
     * - the user is an admin or the author
     */
    public function isStoryEditableBy(Story $story, UserInterface $user): bool
    {
        return $story->isEditable()
            && $this->isAdminOrStoryCreator($story, $user);
    }

    /**
     * Story is public if any:
     * - it has no creator
     * - the creator is an admin
     * ? the story is published
     */
    public function isStoryPublic(Story $story): bool
    {
        $creator = $story->creator();

        return !$creator || $this->isAdmin($creator);
    }

    public function isAdminOrStoryCreator(Story $story, UserInterface $user): bool
    {
        return $this->isAdmin($user)
            || $this->isStoryCreator($story, $user);
    }

    public function isStoryCreator(Story $story, UserInterface $user): bool
    {
        $creator = $story->creator();

        return $creator && $creator->equals($user->toUser());
    }

    public function isAdmin(UserInterface $user): bool
    {
        return $this->telegramUserService->isAdmin(
            $user->toTelegramUser()
        );
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
