<?php

namespace Brightwood\Hydrators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class StoryHydrator extends Hydrator
{
    private StoryVersionRepositoryInterface $storyVersionRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        StoryVersionRepositoryInterface $storyVersionRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->storyVersionRepository = $storyVersionRepository;
        $this->userRepository = $userRepository;
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
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
