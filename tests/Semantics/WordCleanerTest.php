<?php

namespace App\Tests\Semantics;

use App\Semantics\Word\WordCleaner;
use App\Tests\WiredTest;

final class WordCleanerTest extends WiredTest
{
    /**
     * @dataProvider purgeProvider
     */
    public function testPurge(string $word, string $prevWord, string $expected): void
    {
        /** @var WordCleaner $cleaner */
        $cleaner = $this->get(WordCleaner::class);

        $this->assertEquals(
            $expected,
            $cleaner->purge($word, $prevWord)
        );
    }

    public function purgeProvider(): array
    {
        return [
            ['стол', 'стул', 'стол'],
            ['стол стул', 'стул', 'стол'],
            ['стул стол', 'стул', 'стол'],
            ['кредитная карта', 'карта', 'кредитная'],
            ['кредитная карта', 'дебетовая карта', 'кредитная карта'],
            ['бесплатная кредитная карта', 'кредитная карта', 'бесплатная'],
            ['кредитная карта карта кредитная', 'карта', 'кредитная кредитная'],
        ];
    }
}
