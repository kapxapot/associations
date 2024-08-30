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

    public function testWithMeta(): void
    {
        $tgUser = new TelegramUser();

        $this->assertFalse($tgUser->isDirty());

        $tgUser->withMeta([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue($tgUser->isDirty());

        $this->assertEquals('value1', $tgUser->getMetaValue('key1'));
        $this->assertEquals('value2', $tgUser->getMetaValue('key2'));

        $tgUser->withMeta([
            'key1' => 'newValue1',
            'key2' => null,
            'key3' => 'newValue3',
        ]);

        $this->assertTrue($tgUser->isDirty());

        $this->assertEquals('newValue1', $tgUser->getMetaValue('key1'));
        $this->assertNull($tgUser->getMetaValue('key2'));
        $this->assertEquals('newValue3', $tgUser->getMetaValue('key3'));

        $this->assertEquals(
            [
                'key1' => 'newValue1',
                'key3' => 'newValue3',
            ],
            $tgUser->metaData()
        );
    }

    public function testDirtyBotAdmin(): void
    {
        $tgUser = new TelegramUser();

        $this->assertFalse($tgUser->isDirty());

        $tgUser->withBotAdmin(true);

        $this->assertTrue($tgUser->isDirty());
    }

    public function testBotAdminIsNullOnFalse(): void
    {
        $tgUser = new TelegramUser([
            'meta' => json_encode([
                TelegramUser::META_BOT_ADMIN => true
            ])
        ]);

        $this->assertFalse($tgUser->isDirty());

        $tgUser->withBotAdmin(true);

        $this->assertFalse($tgUser->isDirty());

        $tgUser->withBotAdmin(false);

        $this->assertTrue($tgUser->isDirty());

        $this->assertNull($tgUser->getMetaValue(TelegramUser::META_BOT_ADMIN));
    }
}
