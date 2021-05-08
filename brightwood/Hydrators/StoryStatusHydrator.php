<?php

namespace Brightwood\Hydrators;

use Brightwood\Models\StoryStatus;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Repositories\Interfaces\TelegramUserRepositoryInterface;

class StoryStatusHydrator extends Hydrator
{
    private TelegramUserRepositoryInterface $telegramUserRepository;

    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository
    )
    {
        $this->telegramUserRepository = $telegramUserRepository;
    }

    /**
     * @param StoryStatus $entity
     */
    public function hydrate(DbModel $entity): StoryStatus
    {
        return $entity
            ->withTelegramUser(
                fn () => $this->telegramUserRepository->get($entity->telegramUserId)
            );
    }
}
