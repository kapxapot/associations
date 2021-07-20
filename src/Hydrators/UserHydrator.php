<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\User;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\SberUserRepositoryInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\UserService;
use Plasticode\External\Gravatar;
use Plasticode\Hydrators\UserHydrator as BaseUserHydrator;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends BaseUserHydrator
{
    private AliceUserRepositoryInterface $aliceUserRepository;
    private GameRepositoryInterface $gameRepository;
    private SberUserRepositoryInterface $sberUserRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;
    private UserService $userService;

    public function __construct(
        AliceUserRepositoryInterface $aliceUserRepository,
        GameRepositoryInterface $gameRepository,
        RoleRepositoryInterface $roleRepository,
        SberUserRepositoryInterface $sberUserRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        LinkerInterface $linker,
        Gravatar $gravatar,
        UserService $userService
    )
    {
        parent::__construct($roleRepository, $linker, $gravatar);

        $this->aliceUserRepository = $aliceUserRepository;
        $this->gameRepository = $gameRepository;
        $this->sberUserRepository = $sberUserRepository;
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
            )
            ->withAliceUser(
                fn () => $this->aliceUserRepository->getByUser($entity)
            )
            ->withSberUser(
                fn () => $this->sberUserRepository->getByUser($entity)
            )
            ->withPolicy(
                fn () => $this->userService->getUserPolicy($entity)
            );
    }
}
