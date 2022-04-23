<?php

namespace App\Tests\Semantics;

use App\Semantics\SentenceCleaner;
use PHPUnit\Framework\TestCase;

final class SentenceCleanerTest extends TestCase
{
    /**
     * @dataProvider trimTrailingDotProvider
     */
    public function testTrimTrailingDot(?string $original, ?string $expected): void
    {
        $cleaner = new SentenceCleaner();

        $this->assertEquals($expected, $cleaner->trimTrailingDot($original));
    }

    public function trimTrailingDotProvider(): array
    {
        return [
            [null, null],
            ['boo', 'boo'],
            ['one word.', 'one word'],
            ['two three..', 'two three..'],
            ['six seven ten...', 'six seven ten...'],
            ['what?', 'what?'],
            ['noooo!', 'noooo!'],
            ['текст.', 'текст'],
        ];
    }
}
