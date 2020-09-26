<?php

namespace Brightwood\Tests\Models;

use App\Models\TelegramUser;
use Brightwood\Testing\Models\TestData;
use Brightwood\Testing\Models\TestStory;
use PHPUnit\Framework\TestCase;

final class StoryTest extends TestCase
{
    public function testStart() : void
    {
        $story = new TestStory(1);
        $message = $story->start(new TelegramUser());

        $this->assertNotNull($message);
    }

    public function testRenderNode() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData(new TelegramUser());

        $message = $story->renderNode($node, $data);

        $this->assertNotNull($message->data());
    }

    public function testGo() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData(new TelegramUser());

        $this->assertNotNull($data);

        $message = $story->go(
            new TelegramUser(),
            $node,
            'Сесть на пенек и заплакать',
            $data
        );

        $this->assertNotNull($message);
        $this->assertNotNull($message->data());
    }

    public function testDefaultMakeData() : void
    {
        $story = new TestStory(1);
        $data = $story->makeData(new TelegramUser());

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(1, $data->day);
    }

    public function testPredefinedMakeData() : void
    {
        $story = new TestStory(1);
        $data = $story->makeData(new TelegramUser(), ['day' => 2]);

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(2, $data->day);
    }
}
