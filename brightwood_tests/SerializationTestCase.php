<?php

namespace Brightwood\Tests;

use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use PHPUnit\Framework\TestCase;
use Plasticode\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use Plasticode\Testing\Seeders\TelegramUserSeeder;

abstract class SerializationTestCase extends TestCase
{
    protected RootDeserializerInterface $deserializer;
    protected TelegramUserRepositoryInterface $telegramUserRepository;
    protected Player $player;

    public function setUp() : void
    {
        parent::setUp();

        $this->telegramUserRepository = new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        $this->deserializer = RootDeserializerFactory::make();

        $this->player = new Human(
            $this->telegramUserRepository->get(1)
        );

        $this->player->withId('59f628bbf4cb3c3b44ae');

        $this->deserializer->addPlayers($this->player);
    }

    public function tearDown() : void
    {
        unset($this->deserializer);
        unset($this->player);
        unset($this->telegramUserRepository);

        parent::tearDown();
    }
}
