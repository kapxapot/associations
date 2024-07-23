<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class StoryRepository extends IdiormRepository implements StoryRepositoryInterface
{
    protected function entityClass(): string
    {
        return Story::class;
    }

    public function get(?int $id): ?Story
    {
        return $this->getEntity($id);
    }

    public function getAll(): StoryCollection
    {
        return StoryCollection::from(
            $this->query()
        );
    }
}
