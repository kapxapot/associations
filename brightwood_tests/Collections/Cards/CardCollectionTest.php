<?php

namespace Brightwood\Tests\Collections\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\SuitedCardCollection;
use Brightwood\Factories\Cards\FullDeckFactory;
use Brightwood\Models\Cards\Games\EightsGame;
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
    ): void
    {
        $this->assertEquals(
            $expected->toArray(),
            $original->filterSuited()->toArray()
        );
    }

    public function filterSuitedProvider(): array
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

    public function testSort(): void
    {
        $cards = (new FullDeckFactory())->make()->cards();

        $sorted = $cards->sort([EightsGame::class, 'sort']);

        $this->assertTrue($sorted[53]->isRank(Rank::eight()));
        $this->assertTrue($sorted[52]->isRank(Rank::eight()));
        $this->assertTrue($sorted[51]->isRank(Rank::eight()));
        $this->assertTrue($sorted[50]->isRank(Rank::eight()));
        $this->assertTrue($sorted[49] instanceof Joker);
        $this->assertTrue($sorted[48] instanceof Joker);
        $this->assertTrue($sorted[47]->isRank(Rank::king()));
        $this->assertTrue($sorted[0]->isRank(Rank::ace()));
    }

    public function testSortReverse(): void
    {
        $cards = (new FullDeckFactory())->make()->cards();

        $sorted = $cards->sortReverse([EightsGame::class, 'sort']);

        $this->assertTrue($sorted[0]->isRank(Rank::eight()));
        $this->assertTrue($sorted[1]->isRank(Rank::eight()));
        $this->assertTrue($sorted[2]->isRank(Rank::eight()));
        $this->assertTrue($sorted[3]->isRank(Rank::eight()));
        $this->assertTrue($sorted[4] instanceof Joker);
        $this->assertTrue($sorted[5] instanceof Joker);
        $this->assertTrue($sorted[6]->isRank(Rank::king()));
        $this->assertTrue($sorted[53]->isRank(Rank::ace()));
    }
}
