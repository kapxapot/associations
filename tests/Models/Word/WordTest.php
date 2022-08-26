<?php

namespace App\Tests\Models\Word;

use App\Models\Word;
use PHPUnit\Framework\TestCase;

final class WordTest extends TestCase
{
    public function testMainChain(): void
    {
        // $w1 -> $w2 -> $w3

        $w1 = new Word(['id' => 1]);
        $w2 = new Word(['id' => 2]);
        $w3 = new Word(['id' => 3]);

        $w1->withMain($w2);
        $w2->withMain($w3);
        $w3->withMain(null);

        $this->assertEquals([2, 3], $w1->mainChain()->ids()->toArray());
        $this->assertEquals([3], $w2->mainChain()->ids()->toArray());
        $this->assertEquals([], $w3->mainChain()->ids()->toArray());
    }

    public function testDistanceFromAncestor(): void
    {
        // $w0 -> $w1 -> $w2 -> $w3
        // $w4

        $w0 = new Word(['id' => 0]);
        $w1 = new Word(['id' => 1]);
        $w2 = new Word(['id' => 2]);
        $w3 = new Word(['id' => 3]);
        $w4 = new Word(['id' => 4]);

        $w0->withMain($w1);
        $w1->withMain($w2);
        $w2->withMain($w3);
        $w3->withMain(null);
        $w4->withMain(null);

        $this->assertEquals(null, $w1->distanceFromAncestor($w0));
        $this->assertEquals(0, $w1->distanceFromAncestor($w1));
        $this->assertEquals(1, $w1->distanceFromAncestor($w2));
        $this->assertEquals(2, $w1->distanceFromAncestor($w3));
        $this->assertEquals(null, $w1->distanceFromAncestor($w4));

        $this->assertEquals(0, $w2->distanceFromAncestor($w2));
        $this->assertEquals(1, $w2->distanceFromAncestor($w3));

        $this->assertEquals(0, $w3->distanceFromAncestor($w3));
    }
}
