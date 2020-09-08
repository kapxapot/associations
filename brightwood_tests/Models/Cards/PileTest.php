<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Models\Cards\SuitedCard;
use PHPUnit\Framework\TestCase;

final class PileTest extends TestCase
{
    public function testTakeMany() : void
    {
        $pile = new Pile();

        $hearts8 = SuitedCard::tryParse('♥8');
        $hearts2 = SuitedCard::tryParse('♥2');

        $pile->add($hearts8);
        $pile->add($hearts2);

        $this->assertEquals(2, $pile->size());

        $taken = $pile->takeMany(1);

        $this->assertCount(1, $taken);
        $this->assertEquals(1, $pile->size());

        $this->assertTrue(
            $hearts2->equals(
                $taken->first()
            )
        );

        $this->assertTrue(
            $hearts8->equals(
                $pile->cards()->first()
            )
        );
    }
}
