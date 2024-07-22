<?php

use Brightwood\Models\Stories\EightsStory;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Util\Cases;

class EightsStoryFactory
{
    public function __invoke(
        RootDeserializerInterface $rootDeserializer,
        Cases $cases
    ): EightsStory
    {
        $story = new EightsStory(
            ['id' => EightsStory::ID],
            $rootDeserializer,
            $cases
        );

        $story->build();
        $story->checkIntegrity();

        return $story;
    }
}
