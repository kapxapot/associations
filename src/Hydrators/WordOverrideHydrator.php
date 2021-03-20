<?php

namespace App\Hydrators;

use App\Models\WordOverride;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class WordOverrideHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;
    }

    /**
     * @param WordOverride $entity
     */
    public function hydrate(DbModel $entity): WordOverride
    {
        return $entity
            ->withWord(
                fn () => $this->wordRepository->get($entity->wordId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
