<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Testing\Factories\RootDeserializerTestFactory;
use Brightwood\Testing\Factories\TelegramUserRepositoryTestFactory;
use PHPUnit\Framework\TestCase;

abstract class SerializationTestCase extends TestCase
{
    protected TelegramUserRepositoryInterface $telegramUserRepository;

    protected RootDeserializerInterface $deserializer;
    protected Player $player;

    public function setUp(): void
    {
        parent::setUp();

        $this->telegramUserRepository = TelegramUserRepositoryTestFactory::make();
        $this->deserializer = RootDeserializerTestFactory::make();

        $this->player = new Human(
            $this->telegramUserRepository->get(1)
        );

        $this->player->withId('59f628bbf4cb3c3b44ae');

        $this->deserializer->addPlayers($this->player);
    }

    public function tearDown(): void
    {
        unset($this->telegramUserRepository);
        unset($this->deserializer);
        unset($this->player);

        parent::tearDown();
    }
}
