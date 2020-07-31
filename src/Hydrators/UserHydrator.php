<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\UserService;
use Plasticode\External\Gravatar;
use Plasticode\Hydrators\UserHydrator as BaseUserHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends BaseUserHydrator
{
    private GameRepositoryInterface $gameRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;
    private UserService $userService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        RoleRepositoryInterface $roleRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        LinkerInterface $linker,
        Gravatar $gravatar,
        UserService $userService
    )
    {
        parent::__construct($roleRepository, $linker, $gravatar);

        $this->gameRepository = $gameRepository;
        $this->telegramUserRepository = $telegramUserRepository;

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
            ->withIsMature(
                fn () => $this->userService->isMature($entity)
            )
            ->withCurrentGame(
                fn () => $this->gameRepository->getCurrentByUser($entity)
            )
            ->withLastGame(
                fn () => $this->gameRepository->getLastByUser($entity)
            )
            ->withTelegramUser(
                fn () => $this->telegramUserRepository->getByUser($entity)
            );
    }
}
