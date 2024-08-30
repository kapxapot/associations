<?php

namespace Brightwood\Tests\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Models\Data\JsonStoryData;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Services\TelegramUserService;
use Brightwood\Testing\Factories\SettingsProviderTestFactory;
use Brightwood\Testing\Factories\StoryServiceTestFactory;
use PHPUnit\Framework\TestCase;

final class JsonStoryTest extends TestCase
{
    private JsonStory $story;

    public function setUp(): void
    {
        $json = file_get_contents('brightwood_tests/Files/test_story.json');

        $settingsProvider = SettingsProviderTestFactory::make();
        $telegramUserService = new TelegramUserService($settingsProvider);
        $storyService = StoryServiceTestFactory::make($telegramUserService);

        $this->story = $storyService->makeStoryFromJson($json);
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

        $this->assertInstanceOf(JsonStoryData::class, $data);
        $this->assertEquals(1, $data->day);
    }

    public function testLoadData(): void
    {
        $data = $this->story->loadData(['day' => 2]);

        $this->assertInstanceOf(JsonStoryData::class, $data);
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
