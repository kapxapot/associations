<?php

namespace App\Tests\Models;

use App\Models\TelegramUser;
use PHPUnit\Framework\TestCase;

final class TelegramUserTest extends TestCase
{
    public function testIncognitoName(): void
    {
        $tgUser = (new TelegramUser([
            'id' => 123,
            'telegram_id' => 1
        ]))->withUser(null);

        $this->assertEquals('инкогнито 123', $tgUser->name());
    }

    public function testIncognitoChatName(): void
    {
        $tgUser = (new TelegramUser([
            'id' => 123,
            'telegram_id' => -1
        ]))->withUser(null);

        $this->assertEquals('инкогнито чат 123', $tgUser->name());
    }
}
