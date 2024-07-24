<?php

namespace Brightwood\Testing\Mocks\Repositories;

use Brightwood\Collections\StoryVersionCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class StoryVersionRepositoryMock extends RepositoryMock implements StoryVersionRepositoryInterface
{
    private StoryVersionCollection $storyVersions;

    public function __construct()
    {
        $this->storyVersions = StoryVersionCollection::empty();
    }

    public function get(?int $id): ?StoryVersion
    {
        return $this->storyVersions->first(
            fn (StoryVersion $sv) => $sv->getId() == $id
        );
    }

    public function getCurrentVersion(Story $story): ?StoryVersion
    {
        // placeholder
        return null;
    }
}
