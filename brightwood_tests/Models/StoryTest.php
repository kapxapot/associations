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
        $sequence = $story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testRenderNode() : void
    {
        $tgUser = new TelegramUser();
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData();

        $sequence = $story->renderNode($tgUser, $node, $data);

        $this->assertNotNull($sequence->data());
    }

    public function testGo() : void
    {
        $story = new TestStory(1);
        $node = $story->getNode(6);
        $data = $story->makeData();

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
        $data = $story->makeData();

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(1, $data->day);
    }

    public function testPredefinedMakeData() : void
    {
        $story = new TestStory(1);
        $data = $story->makeData(['day' => 2]);

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(2, $data->day);
    }

    public function testFinishNode() : void
    {
        $tgUser = new TelegramUser();
        $story = new TestStory(1);
        $node = $story->getNode(4);
        $data = $story->makeData();

        $sequence = $story->renderNode($tgUser, $node, $data);

        $this->assertEmpty(
            $sequence->actions()
        );

        $this->assertEmpty(
            $sequence->messages()->last()->actions()
        );
    }

    public function testEmptyFinishNode() : void
    {
        $tgUser = new TelegramUser();
        $story = new TestStory(1);
        $node = $story->getNode(8);
        $data = $story->makeData();

        $sequence = $story->renderNode($tgUser, $node, $data);

        $this->assertEmpty(
            $sequence->actions()
        );

        $this->assertEmpty(
            $sequence->messages()->last()->actions()
        );
    }
}
