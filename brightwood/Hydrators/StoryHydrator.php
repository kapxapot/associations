<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Services\StoryService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryHydrator extends Hydrator
{
    private StoryVersionRepositoryInterface $storyVersionRepository;
    private UserRepositoryInterface $userRepository;
    private StoryService $storyService;

    public function __construct(
        StoryVersionRepositoryInterface $storyVersionRepository,
        UserRepositoryInterface $userRepository,
        StoryService $storyService
    )
    {
        $this->storyVersionRepository = $storyVersionRepository;
        $this->userRepository = $userRepository;
        $this->storyService = $storyService;
    }

    /**
     * @param Story $entity
     */
    public function hydrate(DbModel $entity): Story
    {
        return $entity
            ->withCurrentVersion(
                fn () => $this->storyVersionRepository->getCurrentVersion($entity)
            )
            ->withSourceStory(
                fn () => $this->storyService->getStory($entity->sourceStoryId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withDeleter(
                fn () => $this->userRepository->get($entity->deletedBy)
            );
    }
}
