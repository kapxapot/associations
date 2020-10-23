<?php

namespace Brightwood\Tests\Models\Cards;

use App\Models\TelegramUser;
use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Games\EightsGame;
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
use Brightwood\Parsing\StoryParser;
use Brightwood\Tests\SerializationTestCase;
use Plasticode\Util\Cases;

final class EightsDataTest extends SerializationTestCase
{
    public function testSerialize() : void
    {
        $player1 = (new Bot())
            ->withId('929e1b1d93a09b7d5aba')
            ->withName('Гарри')
            ->withIcon('🐷')
            ->withHand(
                new Hand()
            );

        $player2 = (new FemaleBot())
            ->withId('12120dcd2f939d0f3935')
            ->withName('Анна')
            ->withIcon('🐵')
            ->withHand(
                new Hand(
                    CardCollection::collect(
                        Card::parse('♦6'),
                        Card::parse('♣10'),
                        Card::parse('♣5')
                    )
                )
            );

        $player3 = (new FemaleBot())
            ->withId('db39d485e636eab5d47b')
            ->withName('Лиза')
            ->withIcon('🐱')
            ->withHand(
                new Hand(
                    CardCollection::collect(
                        Card::parse('♠5'),
                        Card::parse('♠4'),
                        Card::parse('♣4'),
                        Card::parse('♦2'),
                        Card::parse('♣9')
                    )
                )
            );

        $tgUser = $this->telegramUserRepository->get(1);

        $player4 = (new Human())
            ->withId('6027c68aadaac8c43f13')
            ->withTelegramUser($tgUser)
            ->withHand(
                new Hand(
                    CardCollection::collect(
                        Card::parse('♦Q'),
                        Card::parse('♦9'),
                        Card::parse('♦10'),
                        Card::parse('♠10'),
                        Card::parse('♠2'),
                        Card::parse('♥6'),
                        Card::parse('♠A')
                    )
                )
            );

        $players = PlayerCollection::collect(
            $player1,
            $player2,
            $player3,
            $player4
        );

        $game = new EightsGame(
            new StoryParser(),
            new Cases(),
            $players,
            new Deck() // empty deck
        );

        $game
            ->withDiscard(
                new EightsDiscard(
                    CardCollection::collect(
                        Card::parse('♥J'),
                        Card::parse('♥8')
                            ->withRestriction(
                                new SuitRestriction(
                                    Suit::hearts()
                                )
                            ),
                        Card::parse('♣8')
                            ->withRestriction(
                                new SuitRestriction(
                                    Suit::spades()
                                )
                            ),
                        Card::parse('♠7'),
                        Card::parse('🃏'),
                        Card::parse('♦7'),
                        Card::parse('♣7'),
                        Card::parse('♣K'),
                        Card::parse('♣Q')
                    )
                )
            )
            ->withTrash(new Pile())
            ->withStarter($player1)
            ->withIsStarted(true)
            ->withObserver($player4)
            ->withGift(
                new SixGiftAction(
                    Card::parse('♣6'),
                    $player1
                )
            )
            ->withMove(53)
            ->withNoCardsInARow(0)
            ->withShowPlayersLine(false);

        $data = (new EightsData())->withGame($game);

        $jsonStr = json_encode($data);

        //var_dump($jsonStr);

        $this->assertIsString($jsonStr);

        $expectedJsonStr = file_get_contents('brightwood_tests/Files/eights_data.json');

        $this->assertEquals($expectedJsonStr, $jsonStr . PHP_EOL);
    }

    public function testDeserialize() : void
    {
        $jsonStr = file_get_contents('brightwood_tests/Files/eights_data.json');

        $jsonData = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($jsonData);

        /** @var EightsData */
        $data = $this->deserializer->deserialize($jsonData);

        $this->assertInstanceOf(EightsData::class, $data);

        $game = $data->game();

        $this->assertInstanceOf(EightsGame::class, $game);

        $this->assertEquals(4, $data->playerCount);

        // check players
        $this->assertInstanceOf(PlayerCollection::class, $game->players());
        $this->assertCount(4, $game->players());

        [
            /** @var Bot */
            $player1,
            /** @var FemaleBot */
            $player2,
            /** @var FemaleBot */
            $player3,
            /** @var Human */
            $player4
        ] = $game->players();

        // check 1st player - bot
        $this->assertInstanceOf(Bot::class, $player1);
        $this->assertEquals(Cases::MAS, $player1->gender());
        $this->assertInstanceOf(Hand::class, $player1->hand());
        $this->assertEquals(0, $player1->handSize());

        // check 2nd player - female bot
        $this->assertInstanceOf(FemaleBot::class, $player2);
        $this->assertEquals(Cases::FEM, $player2->gender());

        $this->assertEquals(
            '♦6, ♣10, ♣5',
            $player2->hand()->toString()
        );

        $this->assertTrue(
            $player2->hand()->cards()->first()->equals(
                new SuitedCard(Suit::diamonds(), Rank::six())
            )
        );

        // check 3rd player - female bot
        $this->assertInstanceOf(FemaleBot::class, $player3);
        $this->assertEquals(Cases::FEM, $player3->gender());

        // check 4th player - human
        $this->assertInstanceOf(Human::class, $player4);

        $tgUser = $player4->telegramUser();

        $this->assertInstanceOf(TelegramUser::class, $tgUser);

        $this->assertTrue(
            $tgUser->equals(
                $this->telegramUserRepository->get(1)
            )
        );

        // deck
        $this->assertInstanceOf(Deck::class, $game->deck());
        $this->assertEquals(0, $game->deckSize());

        // discard
        $this->assertInstanceOf(EightsDiscard::class, $game->discard());
        $this->assertEquals(9, $game->discardSize());

        /** @var Card */
        $card = $game->discard()->cards()[1];

        $this->assertTrue($card->hasRestriction());

        /** @var SuitRestriction */
        $restriction = $card->restriction();

        $this->assertInstanceOf(SuitRestriction::class, $restriction);

        $this->assertTrue(
            $restriction->suit()->equals(Suit::hearts())
        );

        /** @var Joker */
        $joker = $game->discard()->cards()[4];

        $this->assertInstanceOf(Joker::class, $joker);

        // trash
        $this->assertInstanceOf(Pile::class, $game->trash());
        $this->assertEquals(0, $game->trash()->size());

        // gift (six)
        $gift = $game->gift();

        $this->assertInstanceOf(SixGiftAction::class, $gift);

        $this->assertTrue(
            $gift->card()->equals(
                Card::parse('♣6')
            )
        );

        $this->assertNotNull($gift->sender());

        $this->assertTrue(
            $gift->sender()->equals($player1)
        );
    }
}
