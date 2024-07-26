<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Services\StoryService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryStatusHydrator extends Hydrator
{
    private StoryVersionRepositoryInterface $storyVersionRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private StoryService $storyService;

    public function __construct(
        StoryVersionRepositoryInterface $storyVersionRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        StoryService $storyService
    )
    {
        $this->storyVersionRepository = $storyVersionRepository;
        $this->telegramUserRepository = $telegramUserRepository;

        $this->storyService = $storyService;
    }

    /**
     * @param StoryStatus $entity
     */
    public function hydrate(DbModel $entity): StoryStatus
    {
        return $entity
            ->withStory(
                fn () => $this->storyService->getStory($entity->storyId)
            )
            ->withStoryVersion(
                fn () => $this->storyVersionRepository->get($entity->storyVersionId)
            )
            ->withTelegramUser(
                fn () => $this->telegramUserRepository->get($entity->telegramUserId)
            );
    }
}
