<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Models\StoryVersion;
use Brightwood\Services\StoryService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryVersionHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    private StoryService $storyService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        StoryService $storyService
    )
    {
        $this->userRepository = $userRepository;

        $this->storyService = $storyService;
    }

    /**
     * @param StoryVersion $entity
     */
    public function hydrate(DbModel $entity): StoryVersion
    {
        return $entity
            ->withStory(
                fn () => $this->storyService->getStory($entity->storyId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
