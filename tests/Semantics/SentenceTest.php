<?php

namespace App\Tests\Semantics;

use App\Semantics\Sentence;
use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Generic\Collection;
use Plasticode\Interfaces\ArrayableInterface;

final class SentenceTest extends TestCase
{
    /**
     * @param array|ArrayableInterface $original
     * 
     * @dataProvider joinProvider
     */
    public function testJoin($original, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::join($original)
        );
    }

    public function joinProvider(): array
    {
        return [
            [[], ''],
            [['a'], 'a'],
            [['a', 'b'], 'a, b'],
            [['a', 'b', 'c'], 'a, b, c'],
            [Collection::collect(1, 2, 3, 4), '1, 2, 3, 4'],
        ];
    }

    public function testJoinAlternativeDelimiters(): void
    {
        $this->assertEquals(
            'a.b.c',
            Sentence::join(['a', 'b', 'c'], '.')
        );
    }

    /**
     * @param array|ArrayableInterface $original
     * 
     * @dataProvider homogeneousJoinProvider
     */
    public function testHomogeneousJoin($original, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::homogeneousJoin($original)
        );
    }

    public function homogeneousJoinProvider(): array
    {
        return [
            [[], ''],
            [['a'], 'a'],
            [['a', 'b'], 'a и b'],
            [['a', 'b', 'c'], 'a, b и c'],
            [Collection::collect(1, 2, 3, 4), '1, 2, 3 и 4'],
        ];
    }

    public function testHomogeneousJoinAlternativeDelimiters(): void
    {
        $this->assertEquals(
            'a.b-c',
            Sentence::homogeneousJoin(['a', 'b', 'c'], '.', '-')
        );
    }
}
