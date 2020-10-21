<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Actions\Eights\JackGiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Data\EightsData;
use Brightwood\Tests\SerializationTestCase;
use Plasticode\Util\Cases;

final class EightsDataTest extends SerializationTestCase
{
    public function testSerialize() : void
    {
        $data = new EightsData(
            $this->telegramUserRepository->get(1)
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

        $players = array_map(
            fn ($p) => $this->deserializer->deserialize($p),
            $playersData
        );

        // check players
        $this->assertIsArray($players);
        $this->assertCount(4, $players);

        // !!! important
        $this->deserializer->addPlayers(...$players);

        // check 1st player - bot
        /** @var Bot */
        $bot = $players[0];

        $this->assertInstanceOf(Bot::class, $bot);
        $this->assertEquals(Cases::MAS, $bot->gender());
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

        // check 2nd player - female bot
        /** @var FemaleBot */
        $femaleBot = $players[1];

        $this->assertInstanceOf(FemaleBot::class, $femaleBot);
        $this->assertEquals(Cases::FEM, $femaleBot->gender());

        // check 4th player - human
        /** @var Human */
        $human = $players[3];

        $this->assertInstanceOf(Human::class, $human);

        $tgUser = $human->telegramUser();

        $this->assertInstanceOf(TelegramUser::class, $tgUser);

        $this->assertTrue(
            $tgUser->equals(
                $this->telegramUserRepository->get(1)
            )
        );

        // deck
        /** @var Deck */
        $deck = $this->deserializer->deserialize($gameData['deck']);

        $this->assertInstanceOf(Deck::class, $deck);
        $this->assertEquals(25, $deck->size());

        // discard
        /** @var EightsDiscard */
        $discard = $this->deserializer->deserialize($gameData['discard']);

        $this->assertInstanceOf(EightsDiscard::class, $discard);
        $this->assertEquals(18, $discard->size());

        /** @var Card */
        $card = $discard->cards()->first();

        $this->assertTrue($card->hasRestriction());

        /** @var SuitRestriction */
        $restriction = $card->restriction();

        $this->assertInstanceOf(SuitRestriction::class, $restriction);

        $this->assertTrue(
            $restriction->suit()->equals(Suit::hearts())
        );

        /** @var Joker */
        $joker = $discard->cards()[11];

        $this->assertInstanceOf(Joker::class, $joker);

        // trash
        /** @var Pile */
        $trash = $this->deserializer->deserialize($gameData['trash']);

        $this->assertInstanceOf(Pile::class, $trash);
        $this->assertEquals(0, $trash->size());

        // gift (jack)
        /** @var JackGiftAction */
        $gift = $this->deserializer->deserialize($gameData['gift']);

        $this->assertInstanceOf(JackGiftAction::class, $gift);

        $this->assertTrue(
            $femaleBot->equals($gift->sender())
        );

        // eight gift
        /** @var EightGiftAction */
        $eightGift = $this->deserializer->deserialize($gameData['eight_gift']);

        $this->assertInstanceOf(EightGiftAction::class, $eightGift);

        $this->assertTrue(
            $eightGift->sender()->equals($femaleBot)
        );

        $this->assertTrue(
            $eightGift->suit()->equals(Suit::clubs())
        );

        $this->assertTrue(
            $eightGift->card()->equals(
                new SuitedCard(Suit::spades(), Rank::eight())
            )
        );
    }
}
