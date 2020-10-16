<?php

namespace App\Testing\Seeders;

use App\Models\TelegramUser;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TelegramUserSeeder implements ArraySeederInterface
{
    /**
     * @return TelegramUser[]
     */
    public function seed() : array
    {
        return [
            new TelegramUser(
                [
                    'id' => 1,
                    'user_id' => 1,
                    'telegram_id' => 123,
                    'username' => 'tg user'
                ]
            )
        ];
    }
}
