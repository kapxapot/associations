<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\JsonDataLoader;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use Brightwood\Testing\Factories\TelegramUserRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Plasticode\Semantics\Gender;
use Plasticode\Util\Cases;

final class EightsStoryTest extends TestCase
{
    private TelegramUserRepositoryInterface $telegramUserRepository;
    private EightsStory $story;

    public function setUp(): void
    {
        parent::setUp();

        $this->telegramUserRepository = TelegramUserRepositoryFactory::make();

        $this->story = new EightsStory(
            RootDeserializerFactory::make(),
            new Cases()
        );
    }

    public function tearDown(): void
    {
        unset($this->telegramUserRepository);
        unset($this->story);

        parent::tearDown();
    }

    public function testStart(): void
    {
        $sequence = $this->story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testMakeData(): void
    {
        $data = $this->story->makeData([
            'type' => EightsData::class,
            'data' => [
                'player_count' => 2,
                'game' => null
            ]
        ]);

        $this->assertInstanceOf(EightsData::class, $data);
    }

    public function testMakeDataFull(): void
    {
        $jsonData = JsonDataLoader::load('brightwood_tests/Files/eights_data_1.json');

        $this->assertIsArray($jsonData);

        $this->telegramUserRepository->store([
            'id' => 2,
            'username' => 'kapxapot',
            'gender_id' => Gender::MAS
        ]);

        $data = $this->story->makeData($jsonData);

        $this->assertInstanceOf(EightsData::class, $data);
    }
}
