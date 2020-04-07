<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\UserService;
use Plasticode\Hydrators\UserHydrator as BaseUserHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends BaseUserHydrator
{
    private GameRepositoryInterface $gameRepository;
    private UserService $userService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        RoleRepositoryInterface $roleRepository,
        LinkerInterface $linker,
        UserService $userService
    )
    {
        parent::__construct($roleRepository, $linker);

        $this->gameRepository = $gameRepository;
        $this->userService = $userService;
    }

    /**
     * @param User $entity
     */
    public function hydrate(DbModel $entity): User
    {
        /** @var User */
        $entity = parent::hydrate($entity);

        return $entity
            ->withMature(
                $this->userService->isMature($entity)
            )
            ->withCurrentGame(
                $this->gameRepository->getCurrentByUser($entity)
            )
            ->withLastGame(
                $this->gameRepository->getLastByUser($entity)
            );
    }
}