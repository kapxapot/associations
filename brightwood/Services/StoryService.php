<?php

namespace Brightwood\Services;

use App\Models\Interfaces\UserInterface;
use App\Models\TelegramUser;
use Brightwood\Answers\Messages;
use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Models\StoryCandidate;
use Brightwood\Models\StoryStatus;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StaticStoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryCandidateRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Util\Uuid;
use Plasticode\Util\Date;

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
        return $this
            ->toRichStories(
                $this->storyRepository->getAllByLanguage($langCode)
            )
            ->where(
                fn (Story $story) => $this->isStoryPlayableBy($story, $tgUser)
            );
    }

    public function getStoriesEditableBy(TelegramUser $tgUser): StoryCollection
    {
        return $this
            ->toRichStories(
                $this->storyRepository->getAll()
            )
            ->where(
                fn (Story $story) => $this->isStoryEditableBy($story, $tgUser)
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

        $candidate ??= StoryCandidate::create([
            'created_by' => $user->getId()
        ]);

        $candidate->withJsonData(
            json_encode($jsonData)
        );

        $candidate->uuid = $jsonData['id'];

        return $this->storyCandidateRepository->save($candidate);
    }

    /**
     * Creates a story + a story version from a story candidate.
     * Then deletes the candidate.
     *
     * @param boolean $fork If it's a fork, a new uuid is generated.
     */
    public function newStory(
        StoryCandidate $storyCandidate,
        bool $fork = false
    ): JsonStory
    {
        $data = $storyCandidate->data();

        $story = Story::create([
            'created_by' => $storyCandidate->createdBy,
            'lang_code' => $data['language'] ?? null,
        ]);

        if ($fork) {
            $story->uuid = Uuid::new();

            $sourceStory = $this->getStoryByUuid($storyCandidate->uuid);

            if ($sourceStory) {
                $story->sourceStoryId = $sourceStory->getId();
            }

            $data['id'] = $story->uuid;
        } else {
            $story->uuid = $storyCandidate->uuid;
        }

        $story = $this->storyRepository->save($story);

        $this->storyVersionRepository->store([
            'story_id' => $story->getId(),
            'json_data' => json_encode($data),
            'created_by' => $storyCandidate->createdBy
        ]);

        $this->deleteStoryCandidate($storyCandidate);

        return new JsonStory($story);
    }

    /**
     * Applies story candidate to the story and a language override as a new story version.
     *
     * The resulting language code priority (from highest to lowest):
     * - $langCode param
     * - story candidate's language
     * - story language
     *
     * @param string|null $langCode If provided, the story language is updated with it instead of the candidate's language.
     */
    public function updateStory(
        Story $story,
        StoryCandidate $storyCandidate,
        ?string $langCode = null
    ): JsonStory
    {
        $language = $langCode ?? $storyCandidate->language();

        if ($language && $story->languageCode() !== $language) {
            $story->langCode = $language;
            $this->storyRepository->save($story);
        }

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
     * Story is deletable by user if any:
     * - the story is deletable
     * - the user is an admin or the author
     */
    public function isStoryDeletableBy(Story $story, UserInterface $user): bool
    {
        return $story->isDeletable()
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

    /**
     * Returns the status story with the status story version applied.
     */
    public function getStatusStory(StoryStatus $status): Story
    {
        return $this->applyVersion(
            $status->story(),
            $status->storyVersion()
        );
    }

    public function validateStatus(StoryStatus $status): StoryMessageSequence
    {
        $validationResult = $this->getStatusStory($status)->validateStatus($status);

        if ($validationResult->isOk()) {
            return StoryMessageSequence::empty();
        }

        return Messages::invalidStoryState(
            $validationResult->errors()
        );
    }

    public function deleteStory(Story $story): Story
    {
        $story->deletedAt = Date::dbNow();
        $deletedStory = $this->storyRepository->save($story);
        $this->deleteFromCache($deletedStory);

        return $deletedStory;
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

    private function addToCache(Story $story): Story
    {
        $this->cache[$story->getId()] = $story;

        return $story;
    }

    private function getFromCache(int $id): ?Story
    {
        return $this->cache[$id] ?? null;
    }

    private function deleteFromCache(Story $story): void
    {
        $id = $story->getId();

        if (array_key_exists($id, $this->cache)) {
            unset($this->cache[$id]);
        }
    }
}
