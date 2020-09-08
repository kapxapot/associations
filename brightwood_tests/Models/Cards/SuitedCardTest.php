<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use PHPUnit\Framework\TestCase;

final class SuitedCardTest extends TestCase
{
    /**
     * @dataProvider tryParseValidCardsProvider
     */
    public function testTryParseValidCards(string $originalStr, SuitedCard $expected) : void
    {
        $this->assertTrue(
            $expected->equals(
                SuitedCard::tryParse($originalStr)
            )
        );
    }

    public function tryParseValidCardsProvider() : array
    {
        return [
            ['♦8', new SuitedCard(Suit::diamonds(), Rank::eight())],
            ['♥7', new SuitedCard(Suit::hearts(), Rank::seven())],
            ['♣Q', new SuitedCard(Suit::clubs(), Rank::queen())],
            ['♠10', new SuitedCard(Suit::spades(), Rank::ten())],
            ['♠T', new SuitedCard(Suit::spades(), Rank::ten())],
        ];
    }

    /**
     * @dataProvider tryParseFailProvider
     */
    public function testTryParseFail(?string $originalStr) : void
    {
        $this->assertNull(
            SuitedCard::tryParse($originalStr)
        );
    }

    public function tryParseFailProvider() : array
    {
        return [
            [null],
            [''],
            ['f'],
            ['💀'],
            ['♣QQ'],
            ['ababa']
        ];
    }
}
