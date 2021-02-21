<?php

namespace App\Hydrators;

use App\Models\AliceUser;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class AliceUserHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param AliceUser $entity
     */
    public function hydrate(DbModel $entity): AliceUser
    {
        return $entity
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
