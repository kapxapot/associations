<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\JsonFileStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Util\Cases;

/**
 * Stub repository for now.
 */
class StoryRepository implements StoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct(
        RootDeserializerInterface $rootDeserializer,
        Cases $cases
    )
    {
        $this->stories = StoryCollection::collect(
            new WoodStory(1),
            new JsonFileStory(2, __DIR__ . '/../Models/Stories/Json/mystery.json', true),
            new EightsStory(3, $rootDeserializer, $cases)
        );
    }

    public function get(?int $id): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->id() === $id
        );
    }

    public function getAllPublished(): StoryCollection
    {
        return $this->stories->where(
            fn (Story $s) => $s->isPublished()
        );
    }
}
