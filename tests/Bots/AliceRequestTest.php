<?php

namespace App\Tests\Bots;

use App\Bots\AliceRequest;
use PHPUnit\Framework\TestCase;

final class AliceRequestTest extends TestCase
{
    /**
     * @dataProvider filterTokensProvider
     */
    public function testFilterTokens(string $originalCommand, string $expectedCommand): void
    {
        $request = new AliceRequest([
            'request' => ['original_utterance' => $originalCommand]
        ]);

        $this->assertEquals($expectedCommand, $request->command());
    }

    public function filterTokensProvider(): array
    {
        return [
            ['спортсмен', 'спортсмен'],
            ['ой спортсмен', 'спортсмен'],
            ['ой спортсмен блин', 'спортсмен'],
            ['спортсмен блин', 'спортсмен'],
            ['блин', 'блин'],
            ['ой блин', 'блин'],
            ['сейчас блин', ''],
        ];
    }
}
