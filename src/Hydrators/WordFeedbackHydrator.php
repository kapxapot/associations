<?php

namespace App\Hydrators;

use App\Models\WordFeedback;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class WordFeedbackHydrator extends Hydrator
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
                fn () => $this->wordRepository->get($entity->wordId)
            )
            ->withDuplicate(
                fn () => $this->wordRepository->get($entity->duplicateId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
