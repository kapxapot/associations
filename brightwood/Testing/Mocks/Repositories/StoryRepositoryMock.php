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

    public function __construct(
        ?ArraySeederInterface $seeder = null
    )
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

    public function getByUuid(string $uuid): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->uuid == $uuid
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }

    public function getAllByLanguage(?string $langCode = null): StoryCollection
    {
        if (!$langCode) {
            return $this->getAll();
        }

        return $this->stories->where(
            fn (Story $s) => $s->langCode() == $langCode
        );
    }

    public function store(array $data): Story
    {
        $story = Story::create($data);
        return $this->save($story);
    }

    private function save(Story $story): Story
    {
        if ($this->stories->contains($story)) {
            return $story;
        }

        if (!$story->isPersisted()) {
            $story->id = $this->stories->nextId();
        }

        $this->stories = $this->stories->add($story);

        // return $this->hydrator->hydrate($version);
        return $story;
    }
}
