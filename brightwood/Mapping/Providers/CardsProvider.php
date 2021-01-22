<?php

namespace Brightwood\Mapping\Providers;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Config\SerializationConfig;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Util\Cases;
use Psr\Container\ContainerInterface;

class CardsProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            SerializationConfig::class =>
                fn (ContainerInterface $c) => new SerializationConfig(
                    $c->get(TelegramUserRepositoryInterface::class),
                    $c->get(StoryParser::class),
                    $c->get(Cases::class)
                ),

            CardSerializer::class =>
                fn (ContainerInterface $c) => new CardSerializer(),

            SuitSerializer::class =>
                fn (ContainerInterface $c) => new SuitSerializer(),

            RootDeserializerInterface::class =>
                fn (ContainerInterface $c) => new RootDeserializer(
                    $c->get(SerializationConfig::class),
                    $c->get(CardSerializer::class),
                    $c->get(SuitSerializer::class)
                ),
        ];
    }
}
