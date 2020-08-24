<?php

namespace App\Tests\Models;

use Brightwood\Repositories\StoryRepository;
use PHPUnit\Framework\TestCase;

class StoryTest extends TestCase
{
    public function testGetMessage() : void
    {
        $storyRepository = new StoryRepository();
        $story = $storyRepository->get(1);
        $node = $story->startNode();
        $message = $node->getMessage();

        $this->assertNotNull($message);
    }
}
