<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class GameHydrator extends Hydrator
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
    public function hydrate(DbModel $entity): Game
    {
        return $entity
            ->withTurns(
                fn () => $this->turnRepository->getAllByGame($entity)
            )
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            )
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUrl(
                fn () => $this->linker->game($entity)
            );
    }
}
