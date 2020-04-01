<?php

namespace App\Hydrators;

use App\Models\WordFeedback;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class WordFeedbackHydrator implements HydratorInterface
{
    private WordRepositoryInterface $wordRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->wordRepository = $wordRepository;
        $this->userRepository = $userRepository;
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
                $this->userRepository->get($entity->createdBy())
            );
    }
}
