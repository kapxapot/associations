<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use Brightwood\JsonDataLoader;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use PHPUnit\Framework\TestCase;
use Plasticode\Util\Cases;

final class EightsStoryTest extends TestCase
{
    private EightsStory $story;

    public function setUp(): void
    {
        parent::setUp();

        $this->story = new EightsStory(
            RootDeserializerFactory::make(),
            new Cases()
        );
    }

    public function tearDown(): void
    {
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
            'type' => EightsData::class, // 'Brightwood\\Models\\Data\\EightsData',
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

        $data = $this->story->makeData($jsonData);

        $this->assertInstanceOf(EightsData::class, $data);
    }
}
