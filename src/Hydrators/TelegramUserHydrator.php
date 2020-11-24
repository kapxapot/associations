<?php

namespace App\Hydrators;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\Basic\DbModel;

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
    public function hydrate(DbModel $entity) : TelegramUser
    {
        return $entity
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
