<?php

namespace Brightwood\Testing\Factories;

use App\Bots\Factories\MessageRendererFactory;
use Brightwood\Config\SerializationConfig;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\Util\Cases;

class RootDeserializerFactory
{
    public static function make(): RootDeserializerInterface
    {
        return new RootDeserializer(
            new SerializationConfig(
                TelegramUserRepositoryFactory::make(),
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
