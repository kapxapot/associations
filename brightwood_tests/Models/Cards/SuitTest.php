<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Models\Cards\Suit;
use PHPUnit\Framework\TestCase;

final class SuitTest extends TestCase
{
    /**
     * @dataProvider tryParseKnownSuitsProvider
     */
    public function testTryParseKnownSuits(string $originalStr, Suit $expected) : void
    {
        $this->assertTrue(
            $expected->equals(
                Suit::tryParse($originalStr)
            )
        );
    }

    public function tryParseKnownSuitsProvider() : array
    {
        return [
            ['♦', Suit::diamonds()],
            ['♥', Suit::hearts()],
            ['♣', Suit::clubs()],
            ['♠', Suit::spades()]
        ];
    }

    /**
     * @dataProvider tryParseFailProvider
     */
    public function testTryParseFail(?string $originalStr) : void
    {
        $this->assertNull(
            Suit::tryParse($originalStr)
        );
    }

    public function tryParseFailProvider() : array
    {
        return [
            [null],
            [''],
            ['f'],
            ['💀'],
            ['ababa']
        ];
    }
}
