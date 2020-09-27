<?php

namespace Brightwood\Tests\Models;

use App\Models\TelegramUser;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Testing\Models\TestData;
use Brightwood\Testing\Models\TestStory;
use PHPUnit\Framework\TestCase;

final class StoryTest extends TestCase
{
    public function testStart() : void
    {
        $story = new TestStory(1);
        $sequence = $story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testRenderNode() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData(new TelegramUser());

        $sequence = $story->renderNode($node, $data);

        $this->assertNotNull($sequence->data());
    }

    public function testGo() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData(new TelegramUser());

        $this->assertNotNull($data);

        $sequence = $story->go(
            new TelegramUser(),
            $node,
            'Сесть на пенек и заплакать',
            $data
        );

        $this->assertNotNull($sequence);
        $this->assertNotNull($sequence->data());
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

    public function testEightsStoryStart() : void
    {
        $story = new EightsStory(3);
        $sequence = $story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testFinishNode() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(4);
        $data = $story->makeData(new TelegramUser());

        $sequence = $story->renderNode($node, $data);

        $this->assertNotEmpty(
            $sequence->actions()
        );

        $this->assertNotEmpty(
            $sequence->messages()->last()->actions()
        );
    }
}
