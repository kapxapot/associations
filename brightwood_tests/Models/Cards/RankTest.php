<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Models\Cards\Rank;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    /**
     * @dataProvider tryParseKnownRanksProvider
     */
    public function testTryParseKnownRanks(string $originalStr, Rank $expected) : void
    {
        $this->assertTrue(
            $expected->equals(
                Rank::tryParse($originalStr)
            )
        );
    }

    public function tryParseKnownRanksProvider() : array
    {
        return [
            ['A', Rank::ace()],
            ['2', Rank::two()],
            ['3', Rank::three()],
            ['4', Rank::four()],
            ['5', Rank::five()],
            ['6', Rank::six()],
            ['7', Rank::seven()],
            ['8', Rank::eight()],
            ['9', Rank::nine()],
            ['10', Rank::ten()],
            ['T', Rank::ten()],
            ['J', Rank::jack()],
            ['Q', Rank::queen()],
            ['K', Rank::king()],
        ];
    }

    /**
     * @dataProvider tryParseFailProvider
     */
    public function testTryParseFail(?string $originalStr) : void
    {
        $this->assertNull(
            Rank::tryParse($originalStr)
        );
    }

    public function tryParseFailProvider() : array
    {
        return [
            [null],
            [''],
            ['f'],
            ['💀'],
            ['ababa'],
            ['1'],
            ['11']
        ];
    }
}
