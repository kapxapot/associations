<?php

namespace Brightwood\Testing\Factories;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;

class TelegramUserRepositoryFactory
{
    private static ?TelegramUserRepositoryInterface $instance = null;

    public static function make(): TelegramUserRepositoryInterface
    {
        self::$instance ??= new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        return self::$instance;
    }
}
