<?php

namespace App\Tests\Collections;

use App\Collections\WordCollection;
use App\Models\Word;
use PHPUnit\Framework\TestCase;

final class WordCollectionTest extends TestCase
{
    public function testOrder(): void
    {
        $col = WordCollection::collect(
            new Word(['id' => 4]),
            new Word(['id' => 2]),
            new Word(['id' => 3]),
            new Word(['id' => 1])
        );

        $this->assertEquals(
            [1, 2, 3, 4],
            $col->order()->ids()->toArray()
        );
    }
}
