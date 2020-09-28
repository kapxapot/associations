<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use PHPUnit\Framework\TestCase;

final class EightsDiscardTest extends TestCase
{
    /**
     * @dataProvider actualTopProvider
     */
    public function testActualTop(EightsDiscard $discard, Card $card) : void
    {
        $this->assertTrue(
            $discard->actualTop()->equals($card)
        );
    }

    public function actualTopProvider() : array
    {
        $clubs8 = new SuitedCard(Suit::clubs(), Rank::eight());
        $joker = new Joker();

        $restricting = new SuitedCard(Suit::clubs(), Rank::eight());

        $restricting->addRestriction(
            new EightGiftAction(
                $restricting,
                Suit::hearts(),
                new Bot('bot')
            )
        );

        return [
            [
                (new EightsDiscard())
                    ->add($joker),
                $joker
            ],
            [
                (new EightsDiscard())
                    ->add($clubs8)
                    ->add($joker),
                $clubs8
            ],
            [
                (new EightsDiscard())
                    ->add($restricting)
                    ->add($joker),
                $restricting
            ]
        ];
    }

    /**
     * @dataProvider topStringProvider
     */
    public function testTopString(EightsDiscard $discard, string $str) : void
    {
        $this->assertEquals(
            $str,
            $discard->topString()
        );
    }

    public function topStringProvider() : array
    {
        $clubs8 = new SuitedCard(Suit::clubs(), Rank::eight());
        $joker = new Joker();

        $restricting = new SuitedCard(Suit::clubs(), Rank::eight());

        $restricting->addRestriction(
            new EightGiftAction(
                $restricting,
                Suit::hearts(),
                new Bot('bot')
            )
        );

        return [
            [
                (new EightsDiscard())
                    ->add($joker),
                $joker->toString()
            ],
            [
                (new EightsDiscard())
                    ->add($clubs8)
                    ->add($joker),
                $joker . ' (' . $clubs8 . ')'
            ],
            [
                (new EightsDiscard())
                    ->add($restricting)
                    ->add($joker),
                $joker . ' (' . $restricting->restriction() . ')'
            ]
        ];
    }
}
