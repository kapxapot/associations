<?php

namespace App\Hydrators;

use App\Models\WordRelation;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class WordRelationHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;
    }

    /**
     * @param WordRelation $entity
     */
    public function hydrate(DbModel $entity): WordRelation
    {
        return $entity
            ->withType(
                fn () => $this->wordRelationTypeRepository->get($entity->typeId)
            )
            ->withWord(
                fn () => $this->wordRepository->get($entity->wordId)
            )
            ->withMainWord(
                fn () => $this->wordRepository->get($entity->mainWordId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
