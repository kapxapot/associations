<?php

use Brightwood\Testing\Models\TestStory;

class TestStoryFactory
{
    public function __invoke(): TestStory
    {
        $story = new TestStory(['id' => 1]);

        $story->build();
        $story->checkIntegrity();

        return $story;
    }
}
