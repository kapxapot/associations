<?php

use Brightwood\Models\Stories\EightsStory;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Util\Cases;

class EightsStoryFactory
{
    public function __invoke(
        RootDeserializerInterface $rootDeserializer,
        Cases $cases
    )
    {
        return new EightsStory($rootDeserializer, $cases);
    }
}
