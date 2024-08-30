<?php

namespace Brightwood\Tests\Models\Messages;

use Brightwood\Models\Messages\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public function testEmpty(): void
    {
        $message = new Message();
        $this->assertEmpty($message->lines());
        $this->assertEmpty($message->actions());
    }

    public function testLines(): void
    {
        $message = new Message(['one', 'two']);
        $this->assertEquals(['one', 'two'], $message->lines());
    }

    public function testEmptyLines(): void
    {
        $message = new Message(['one', null, 'two', '', 'three']);
        $this->assertEquals(['one', 'two', 'three'], $message->lines());
    }
}
