<?php

namespace Brightwood\Testing\Mocks\Repositories;

use Brightwood\Collections\StoryVersionCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryVersion;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class StoryVersionRepositoryMock extends RepositoryMock implements StoryVersionRepositoryInterface
{
    private StoryVersionCollection $versions;

    public function __construct()
    {
        $this->versions = StoryVersionCollection::empty();
    }

    public function get(?int $id): ?StoryVersion
    {
        return $this->versions->first(
            fn (StoryVersion $sv) => $sv->getId() == $id
        );
    }

    public function getCurrentVersion(Story $story): ?StoryVersion
    {
        // placeholder
        return null;
    }

    public function store(array $data): StoryVersion
    {
        $version = StoryVersion::create($data);
        return $this->save($version);
    }

    private function save(StoryVersion $version): StoryVersion
    {
        if ($this->versions->contains($version)) {
            return $version;
        }

        if (!$version->isPersisted()) {
            $version->id = $this->versions->nextId();
        }

        $this->versions = $this->versions->add($version);

        // return $this->hydrator->hydrate($version);
        return $version;
    }
}
