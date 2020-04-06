<?php

namespace App\Tests\Validation\Rules;

use App\Validation\Rules\WordIsValid;
use PHPUnit\Framework\TestCase;

final class WordIsValidTest extends TestCase
{
    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid(string $word, bool $expected) : void
    {
        $validator = new WordIsValid();

        $this->assertEquals($expected, $validator->validate($word));
    }

    public function isValidProvider() : array
    {
        return [
            'latinValid' => ['ab a-b\'a', true],
            'latinInvalid' => ['aba, ba', false],
            'cyrillicValid' => ['аб а-ба', true],
            'cyrillicInvalid' => ['аба, ба', false],
            'digits' => ['123 456', true],
            'mixed' => ['ab678 a-b\'a аб а-ба456 123', true],
        ];
    }
}
