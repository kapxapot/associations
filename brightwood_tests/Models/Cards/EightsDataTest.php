<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;
use Brightwood\Config\SerializationConfig;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Data\EightsData;
use Brightwood\Serialization\Interfaces\JsonDeserializerInterface;
use Brightwood\Serialization\UniformDeserializer;
use PHPUnit\Framework\TestCase;

final class EightsDataTest extends TestCase
{
    private JsonDeserializerInterface $deserializer;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    public function setUp() : void
    {
        parent::setUp();

        $this->telegramUserRepository = new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        $this->deserializer = new UniformDeserializer(
            new SerializationConfig($this->telegramUserRepository)
        );
    }

    public function tearDown() : void
    {
        unset($this->deserializer);
        unset($this->telegramUserRepository);

        parent::tearDown();
    }

    public function testSerialize() : void
    {
        $data = new EightsData(
            new TelegramUser(
                [
                    'id' => 1,
                    'user_id' => 1,
                    'telegram_id' => 123,
                    'username' => 'tg user'
                ]
            )
        );

        $data->setPlayerCount(4);

        $data->initGame();
        $data->game()->start();
        $data->game()->run();

        $jsonStr = json_encode($data);

        $this->assertIsString($jsonStr);

        $jsonData = json_decode($jsonStr, true);

        $this->assertIsArray($jsonData);
    }

    public function testDeserialize() : void
    {
        $jsonStr = file_get_contents('brightwood_tests/Files/eights_data.json');

        $data = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        $playerCount = $data['player_count'];
        $humanId = $data['human_id'];

        $this->assertEquals(4, $playerCount);
        $this->assertIsString($humanId);

        $gameData = $data['game'];

        $this->assertIsArray($gameData);

        $playersData = $gameData['players'];

        $this->assertIsArray($playersData);
        $this->assertCount(4, $playersData);

        /** @var Bot */
        $bot = $this->deserializer->deserialize($playersData[0]);

        $this->assertInstanceOf(Bot::class, $bot);
        $this->assertInstanceOf(Hand::class, $bot->hand());
        $this->assertEquals(4, $bot->handSize());

        $this->assertEquals(
            '♣3, ♦J, ♥5, ♦Q',
            $bot->hand()->toString()
        );

        $this->assertTrue(
            $bot->hand()->cards()->first()->equals(
                new SuitedCard(Suit::clubs(), Rank::three())
            )
        );
    }
}
