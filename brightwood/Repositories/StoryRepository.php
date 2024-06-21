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
        $jsonDir = __DIR__ . '/../Models/Stories/Json/';

        $this->stories = StoryCollection::collect(
            new WoodStory(1),
            new JsonFileStory(2, $jsonDir . 'mystery.json', true),
            new EightsStory(3, $rootDeserializer, $cases),
            new JsonFileStory(6, $jsonDir . '359e097f-5620-477b-930d-48496393f747.json', true)
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
