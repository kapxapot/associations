<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use EightsStoryFactory;
use PHPUnit\Framework\TestCase;
use Plasticode\Util\Cases;

final class EightsStoryTest extends TestCase
{
    private RootDeserializerInterface $rootDeserializer;
    private EightsStory $story;

    public function setUp() : void
    {
        parent::setUp();

        $this->rootDeserializer = RootDeserializerFactory::make();

        $factory = new EightsStoryFactory();

        $this->story = ($factory)($this->rootDeserializer, new Cases());
    }

    public function tearDown() : void
    {
        unset($this->story);
        unset($this->rootDeserializer);

        parent::tearDown();
    }

    public function testStart() : void
    {
        $sequence = $this->story->start(new TelegramUser());

        $this->assertNotNull($sequence);
    }

    public function testMakeData() : void
    {
        $data = $this->story->makeData(
            [
                'type' => 'Brightwood\\Models\\Data\\EightsData',
                'data' => [
                    'player_count' => 2,
                    'game' => null
                ]
            ]
        );

        $this->assertInstanceOf(EightsData::class, $data);
    }

    public function testMakeDataFull() : void
    {
        $jsonStr = file_get_contents('brightwood_tests/Files/eights_data_1.json');

        $jsonData = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($jsonData);

        $data = $this->story->makeData($jsonData);

        $this->assertInstanceOf(EightsData::class, $data);
    }
}
