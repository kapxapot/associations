<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;
use Brightwood\Config\SerializationConfig;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use PHPUnit\Framework\TestCase;

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

        $this->deserializer = new RootDeserializer(
            new SerializationConfig($this->telegramUserRepository),
            new CardSerializer(),
            new SuitSerializer()
        );

        $this->player = new Human(
            $this->telegramUserRepository->get(1)
        );

        $this->player->withId('59f628bbf4cb3c3b44ae');

        $this->deserializer->addPlayers($this->player);
    }

    public function tearDown() : void
    {
        unset($this->deserializer);
        unset($this->telegramUserRepository);

        parent::tearDown();
    }
}
