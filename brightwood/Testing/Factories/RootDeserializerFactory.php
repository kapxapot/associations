<?php

namespace Brightwood\Testing\Factories;

use Brightwood\Config\SerializationConfig;
use Brightwood\Parsing\StoryParserFactory;
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
                (new StoryParserFactory())(),
                new Cases()
            ),
            new CardSerializer(),
            new SuitSerializer()
        );
    }
}
