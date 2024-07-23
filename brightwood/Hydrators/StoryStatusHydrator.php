<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryStatusHydrator extends Hydrator
{
    private StoryRepositoryInterface $storyRepository;
    private StoryVersionRepositoryInterface $storyVersionRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository,
        StoryRepositoryInterface $storyRepository,
        StoryVersionRepositoryInterface $storyVersionRepository
    )
    {
        $this->storyRepository = $storyRepository;
        $this->storyVersionRepository = $storyVersionRepository;
        $this->telegramUserRepository = $telegramUserRepository;
    }

    /**
     * @param StoryStatus $entity
     */
    public function hydrate(DbModel $entity): StoryStatus
    {
        return $entity
            ->withStory(
                fn () => $this->storyRepository->get($entity->storyId)
            )
            ->withStoryVersion(
                fn () => $this->storyVersionRepository->get($entity->storyVersionId)
            )
            ->withTelegramUser(
                fn () => $this->telegramUserRepository->get($entity->telegramUserId)
            );
    }
}
