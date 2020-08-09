<?php

namespace App\Hydrators;

use App\Models\TelegramUser;
use App\Repositories\Brightwood\Interfaces\StoryStatusRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class TelegramUserHydrator extends Hydrator
{
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        StoryStatusRepositoryInterface $storyStatusRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->storyStatusRepository = $storyStatusRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param TelegramUser $entity
     */
    public function hydrate(DbModel $entity) : TelegramUser
    {
        return $entity
            ->withStoryStatus(
                fn () => $this->storyStatusRepository->getByTelegramUser($entity)
            )
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
