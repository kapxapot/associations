<?php

namespace App\Tests\Semantics;

use App\Semantics\KeyboardTranslator;
use PHPUnit\Framework\TestCase;

final class KeyboardTranslatorTest extends TestCase
{
    /**
     * @dataProvider translateProvider
     */
    public function testTranslate(
        string $direction,
        ?string $value,
        ?string $expected
    ): void
    {
        $translator = new KeyboardTranslator();

        $this->assertEquals(
            $expected,
            $translator->translate($direction, $value)
        );
    }

    public function translateProvider(): array
    {
        return [
            [KeyboardTranslator::EN_RU, null, null],
            [KeyboardTranslator::EN_RU, 'ghbdtn', 'привет'],
            [KeyboardTranslator::EN_RU, 'русский', 'русский'],
            [KeyboardTranslator::RU_EN, 'руддщ', 'hello'],
            [KeyboardTranslator::RU_EN, 'hello', 'hello'],
        ];
    }
}
