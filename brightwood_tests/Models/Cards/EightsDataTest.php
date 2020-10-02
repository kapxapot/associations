<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use Brightwood\Models\Data\EightsData;
use PHPUnit\Framework\TestCase;

final class EightsDataTest extends TestCase
{
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
    }
}
