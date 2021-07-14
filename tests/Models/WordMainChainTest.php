<?php

namespace App\Tests\Models;

use App\Models\Word;
use PHPUnit\Framework\TestCase;

final class WordMainChainTest extends TestCase
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
}
