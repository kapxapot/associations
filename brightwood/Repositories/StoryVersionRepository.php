<?php

namespace Brightwood\Repositories;

use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class StoryVersionRepository extends IdiormRepository implements StoryVersionRepositoryInterface
{
    protected function entityClass(): string
    {
        return StoryVersion::class;
    }

    public function get(?int $id): ?StoryVersion
    {
        return $this->getEntity($id);
    }

    public function getCurrentVersion(Story $story): ?StoryVersion
    {
        return $this
            ->query()
            ->where('story_id', $story->getId())
            ->orderByDesc('created_at')
            ->one();
    }

    public function store(array $data): StoryVersion
    {
        return $this->storeEntity($data);
    }
}
