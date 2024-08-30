<?php

namespace Brightwood\Tests\Models\Messages;

use Brightwood\Models\Messages\TextMessage;
use PHPUnit\Framework\TestCase;

final class TextMessageTest extends TestCase
{
    public function testEmpty(): void
    {
        $message = new TextMessage();
        $this->assertEmpty($message->lines());
        $this->assertEmpty($message->actions());
    }

    public function testLines(): void
    {
        $message = new TextMessage('one', 'two');
        $this->assertEquals(['one', 'two'], $message->lines());
    }

    public function testEmptyLines(): void
    {
        $message = new TextMessage('one', null, 'two', '', 'three');
        $this->assertEquals(['one', 'two', 'three'], $message->lines());
    }
}
