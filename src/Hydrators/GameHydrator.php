<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class GameHydrator implements HydratorInterface
{
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;

    private LinkerInterface $linker;

    public function __construct(
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        LinkerInterface $linker
    )
    {
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;

        $this->linker = $linker;
    }

    /**
     * @param Game $entity
     */
    public function hydrate(DbModel $entity) : Game
    {
        return $entity
            ->withTurns(
                $this->turnRepository->getAllByGame($entity)
            )
            ->withLanguage(
                $this->languageRepository->get($entity->languageId)
            )
            ->withUser(
                $this->userRepository->get($entity->userId)
            )
            ->withUrl(
                $this->linker->game($entity)
            );
    }
}
