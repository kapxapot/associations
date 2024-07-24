<?php

namespace Brightwood\Tests\Models;

use App\Models\TelegramUser;
use Brightwood\JsonDataLoader;
use Brightwood\Models\Data\JsonStoryData;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryVersion;
use PHPUnit\Framework\TestCase;

final class JsonStoryTest extends TestCase
{
    private JsonStory $story;

    public function setUp(): void
    {
        $json = file_get_contents('brightwood_tests/Files/test_story.json');
        $jsonData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $story = new Story([
            'id' => 1,
            'uuid' => $jsonData['id']
        ]);

        $story->withCurrentVersion(new StoryVersion(['json_data' => $json]));

        $this->story = new JsonStory($story);
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
        $data = $this->story->makeData();

        $sequence = $this->story->renderNode($tgUser, $node, $data);

        $this->assertNotNull($sequence->data());
    }

    public function testGo(): void
    {
        $node = $this->story->getNode(6);
        $data = $this->story->makeData();

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

    public function testDefaultMakeData(): void
    {
        $data = $this->story->makeData();

        $this->assertInstanceOf(JsonStoryData::class, $data);
        $this->assertEquals(1, $data->day);
    }

    public function testPredefinedMakeData(): void
    {
        $data = $this->story->makeData(['day' => 2]);

        $this->assertInstanceOf(JsonStoryData::class, $data);
        $this->assertEquals(2, $data->day);
    }

    public function testFinishNode(): void
    {
        $tgUser = new TelegramUser();
        $node = $this->story->getNode(4);
        $data = $this->story->makeData();

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
        $data = $this->story->makeData();

        $sequence = $this->story->renderNode($tgUser, $node, $data);

        $this->assertEmpty(
            $sequence->actions()
        );

        $this->assertEmpty(
            $sequence->messages()->last()->actions()
        );
    }
}
