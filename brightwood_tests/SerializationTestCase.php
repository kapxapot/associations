<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use PHPUnit\Framework\TestCase;

abstract class SerializationTestCase extends TestCase
{
    protected static ?TelegramUserRepositoryInterface $telegramUserRepository = null;

    protected RootDeserializerInterface $deserializer;
    protected Player $player;

    public function setUp(): void
    {
        parent::setUp();

        if (!self::$telegramUserRepository) {
            self::$telegramUserRepository = new TelegramUserRepositoryMock(
                new TelegramUserSeeder()
            );
        }

        $this->deserializer = RootDeserializerFactory::make(
            self::$telegramUserRepository
        );

        $this->player = new Human(
            self::$telegramUserRepository->get(1)
        );

        $this->player->withId('59f628bbf4cb3c3b44ae');

        $this->deserializer->addPlayers($this->player);
    }

    public function tearDown(): void
    {
        unset($this->deserializer);
        unset($this->player);

        parent::tearDown();
    }
}
