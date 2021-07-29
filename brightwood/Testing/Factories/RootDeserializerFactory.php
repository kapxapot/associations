<?php

namespace Brightwood\Testing\Factories;

use App\Bots\Factories\MessageRendererFactory;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;
use Brightwood\Config\SerializationConfig;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\Util\Cases;

class RootDeserializerFactory
{
    public static function make(
        ?TelegramUserRepositoryInterface $telegramUserRepository = null
    ) : RootDeserializerInterface
    {
        $telegramUserRepository ??= new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        return new RootDeserializer(
            new SerializationConfig(
                $telegramUserRepository,
                new StoryParser(
                    new MessageRendererFactory()
                ),
                new Cases()
            ),
            new CardSerializer(),
            new SuitSerializer()
        );
    }
}
