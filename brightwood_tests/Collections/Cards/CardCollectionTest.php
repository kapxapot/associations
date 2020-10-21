<?php

namespace Brightwood\Tests\Collections\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\SuitedCardCollection;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use PHPUnit\Framework\TestCase;

final class CardCollectionTest extends TestCase
{
    /**
     * @dataProvider filterSuitedProvider
     */
    public function testFilterSuited(
        CardCollection $original,
        SuitedCardCollection $expected
    ) : void
    {
        $this->assertEquals(
            $expected->toArray(),
            $original->filterSuited()->toArray()
        );
    }

    public function filterSuitedProvider() : array
    {
        return [
            [
                CardCollection::empty(),
                SuitedCardCollection::empty()
            ],
            [
                CardCollection::collect(
                    new SuitedCard(Suit::clubs(), Rank::eight()),
                    new SuitedCard(Suit::hearts(), Rank::jack()),
                    new SuitedCard(Suit::spades(), Rank::ace())
                ),
                SuitedCardCollection::collect(
                    new SuitedCard(Suit::clubs(), Rank::eight()),
                    new SuitedCard(Suit::hearts(), Rank::jack()),
                    new SuitedCard(Suit::spades(), Rank::ace())
                )
            ],
            [
                CardCollection::collect(
                    new SuitedCard(Suit::clubs(), Rank::eight()),
                    new Joker(),
                    new SuitedCard(Suit::spades(), Rank::ace())
                ),
                SuitedCardCollection::collect(
                    new SuitedCard(Suit::clubs(), Rank::eight()),
                    new SuitedCard(Suit::spades(), Rank::ace())
                )
            ]
        ];
    }
}
