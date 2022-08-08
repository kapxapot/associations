<?php

namespace App\Tests\Models\DTO;

use App\Models\DTO\TelegramBotInfo;
use PHPUnit\Framework\TestCase;

final class TelegramBotInfoTest extends TestCase
{
    public function testToken(): void
    {
        $token = '123:token';
        $info = new TelegramBotInfo($token);

        $this->assertEquals($token, $info->token());
    }

    public function testId(): void
    {
        $token = '123:token';
        $info = new TelegramBotInfo($token);

        $this->assertEquals(123, $info->id());
    }
}
