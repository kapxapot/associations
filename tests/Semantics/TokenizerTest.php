<?php

namespace App\Tests\Semantics;

use App\Semantics\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    private Tokenizer $tokenizer;

    public function setUp(): void
    {
        $this->tokenizer = new Tokenizer();
    }

    public function tearDown(): void
    {
        unset($this->tokenizer);
    }

    public function testNull(): void
    {
        $this->assertEquals(
            [],
            $this->tokenizer->tokenize(null)
        );
    }

    public function testEmpty(): void
    {
        $this->assertEquals(
            [],
            $this->tokenizer->tokenize('')
        );
    }

    public function testOne(): void
    {
        $this->assertEquals(
            ['abcde'],
            $this->tokenizer->tokenize('abcde')
        );
    }

    public function testMany(): void
    {
        $this->assertEquals(
            ['ab', 'cd', 'e'],
            $this->tokenizer->tokenize('ab cd e')
        );
    }

    public function testCustomDelimiter(): void
    {
        $this->assertEquals(
            ['ab', 'cd', 'e'],
            $this->tokenizer->tokenize('ab+cd+e', '+')
        );
    }

    public function testJoinEmpty(): void
    {
        $this->assertEquals(
            '',
            $this->tokenizer->join([])
        );
    }

    public function testJoinOne(): void
    {
        $this->assertEquals(
            'abcde',
            $this->tokenizer->join(['abcde'])
        );
    }

    public function testJoinMany(): void
    {
        $this->assertEquals(
            'ab cd e',
            $this->tokenizer->join(['ab', 'cd', 'e'])
        );
    }

    public function testJoinCustomDelimiter(): void
    {
        $this->assertEquals(
            'ab+cd+e',
            $this->tokenizer->join(['ab', 'cd', 'e'], '+')
        );
    }
}
