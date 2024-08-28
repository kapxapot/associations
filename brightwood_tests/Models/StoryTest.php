<?php

namespace Brightwood\Tests\Models;

use App\Models\TelegramUser;
use Brightwood\Testing\Models\TestData;
use Brightwood\Testing\Models\TestStory;
use PHPUnit\Framework\TestCase;

final class StoryTest extends TestCase
{
    private ?TestStory $story = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->story = new TestStory();
    }

    public function testProperties(): void
    {
        $this->assertEquals(TestStory::ID, $this->story->getId());
        $this->assertEquals(TestStory::TITLE, $this->story->title());
        $this->assertEquals(TestStory::DESCRIPTION, $this->story->description());
    }

    public function testStart(): void
    {
        $sequence = $this->story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testRenderNode(): void
    {
        $tgUser = new TelegramUser();
        $node = $this->story->getNode(6);
        $data = $this->story->newData();

        $sequence = $this->story->renderNode($tgUser, $node, $data);

        $this->assertNotNull($sequence->data());
    }

    public function testGo(): void
    {
        $node = $this->story->getNode(6);
        $data = $this->story->newData();

        $this->assertNotNull($data);

        $sequence = $this->story->go(
            new TelegramUser(),
            $node,
            $data,
            'Сесть на пенек и заплакать'
        );

        $this->assertNotNull($sequence);
        $this->assertNotNull($sequence->data());
    }

    public function testNewData(): void
    {
        $data = $this->story->newData();

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(1, $data->day);
    }

    public function testLoadData(): void
    {
        $data = $this->story->loadData(['day' => 2]);

        $this->assertInstanceOf(TestData::class, $data);
        $this->assertEquals(2, $data->day);
    }

    public function testFinishNode(): void
    {
        $tgUser = new TelegramUser();
        $node = $this->story->getNode(4);
        $data = $this->story->newData();

        $sequence = $this->story->renderNode($tgUser, $node, $data);

        $this->assertEmpty(
            $sequence->actions()
        );

        $this->assertEmpty(
            $sequence->messages()->last()->actions()
        );
    }

    public function testEmptyFinishNode(): void
    {
        $tgUser = new TelegramUser();
        $node = $this->story->getNode(8);
        $data = $this->story->newData();

        $sequence = $this->story->renderNode($tgUser, $node, $data);

        $this->assertEmpty(
            $sequence->actions()
        );

        $this->assertEmpty(
            $sequence->messages()->last()->actions()
        );
    }
}
