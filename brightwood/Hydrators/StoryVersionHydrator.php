<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryVersionHydrator extends Hydrator
{
    private StoryRepositoryInterface $storyRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        StoryRepositoryInterface $storyRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->storyRepository = $storyRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param StoryVersion $entity
     */
    public function hydrate(DbModel $entity): StoryVersion
    {
        return $entity
            ->withStory(
                fn () => $this->storyRepository->get($entity->storyId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
