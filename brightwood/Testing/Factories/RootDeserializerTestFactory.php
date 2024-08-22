<?php

namespace Brightwood\Testing\Factories;

use Brightwood\Config\SerializationConfig;
use Brightwood\Parsing\StoryParserFactory;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\Util\Cases;

class RootDeserializerTestFactory
{
    public static function make(): RootDeserializerInterface
    {
        $storyParserFactory = new StoryParserFactory(
            new TranslatorTestFactory()
        );

        return new RootDeserializer(
            new SerializationConfig(
                TelegramUserRepositoryTestFactory::make(),
                ($storyParserFactory)(),
                new Cases()
            ),
            new CardSerializer(),
            new SuitSerializer()
        );
    }
}
