<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Models\StoryCandidate;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryCandidateHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param StoryCandidate $entity
     */
    public function hydrate(DbModel $entity): StoryCandidate
    {
        return $entity
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
