<?php

use Brightwood\Models\Stories\WoodStory;

class WoodStoryFactory
{
    public function __invoke(): WoodStory
    {
        $story = new WoodStory(
            ['id' => WoodStory::ID]
        );

        $story->build();
        $story->checkIntegrity();

        return $story;
    }
}
