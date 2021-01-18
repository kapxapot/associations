<?php

namespace App\Hydrators;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

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
