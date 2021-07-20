<?php

namespace App\Hydrators;

use App\Models\SberUser;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class SberUserHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param SberUser $entity
     */
    public function hydrate(DbModel $entity): SberUser
    {
        return $entity
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
