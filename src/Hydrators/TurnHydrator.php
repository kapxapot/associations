<?php

namespace App\Hydrators;

use App\Models\Turn;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class TurnHydrator extends Hydrator
{
    private AssociationRepositoryInterface $associationRepository;
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        GameRepositoryInterface $gameRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository
    )
    {
        $this->associationRepository = $associationRepository;
        $this->gameRepository = $gameRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;
    }

    /**
     * @param Turn $entity
     */
    public function hydrate(DbModel $entity): Turn
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withWord(
                fn () => $this->wordRepository->get($entity->wordId)
            )
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            )
            ->withAssociation(
                fn () => $this->associationRepository->get($entity->associationId)
            )
            ->withPrev(
                fn () => $this->turnRepository->get($entity->prevTurnId)
            );
    }
}
