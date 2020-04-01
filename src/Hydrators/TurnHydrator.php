<?php

namespace App\Hydrators;

use App\Models\Turn;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class TurnHydrator implements HydratorInterface
{
    private GameRepositoryInterface $gameRepository;
    private WordRepositoryInterface $wordRepository;
    private UserRepositoryInterface $userRepository;
    private AssociationRepositoryInterface $associationRepository;
    private TurnRepositoryInterface $turnRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        WordRepositoryInterface $wordRepository,
        UserRepositoryInterface $userRepository,
        AssociationRepositoryInterface $associationRepository,
        TurnRepositoryInterface $turnRepository
    )
    {
        $this->gameRepository = $gameRepository;
        $this->wordRepository = $wordRepository;
        $this->userRepository = $userRepository;
        $this->associationRepository = $associationRepository;
        $this->turnRepository = $turnRepository;
    }

    /**
     * @param Turn $entity
     */
    protected function hydrate(DbModel $entity) : Turn
    {
        return $entity
            ->withGame(
                $this->gameRepository->get($entity->gameId)
            )
            ->withWord(
                $this->wordRepository->get($entity->wordId)
            )
            ->withUser(
                $this->userRepository->get($entity->userId)
            )
            ->withAssociation(
                $this->associationRepository->get($entity->associationId)
            )
            ->withPrev(
                $this->turnRepository->get($entity->prevTurnId)
            );
    }
}
