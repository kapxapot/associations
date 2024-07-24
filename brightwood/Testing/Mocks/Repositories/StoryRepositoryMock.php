<?php

namespace Brightwood\Testing\Mocks\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class StoryRepositoryMock extends RepositoryMock implements StoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct(?ArraySeederInterface $seeder = null)
    {
        $this->stories = $seeder
            ? StoryCollection::make($seeder->seed())
            : StoryCollection::empty();
    }

    public function get(?int $id): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->getId() == $id
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }
}
