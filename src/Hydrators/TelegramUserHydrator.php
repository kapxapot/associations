<?php

namespace App\Hydrators;

use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\TelegramUser;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class TelegramUserHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param TelegramUser $entity
     */
    public function hydrate(DbModel $entity): TelegramUser
    {
        return $entity
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
