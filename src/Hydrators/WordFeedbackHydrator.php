<?php

namespace App\Hydrators;

use App\Models\WordFeedback;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class WordFeedbackHydrator implements HydratorInterface
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
     * @param WordFeedback $entity
     */
    public function hydrate(DbModel $entity) : WordFeedback
    {
        return $entity
            ->withWord(
                $this->wordRepository->get($entity->wordId)
            )
            ->withDuplicate(
                $this->wordRepository->get($entity->duplicateId)
            )
            ->withCreator(
                $this->userRepository->get($entity->createdBy)
            );
    }
}
