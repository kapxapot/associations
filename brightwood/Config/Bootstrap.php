<?php

namespace Brightwood\Config;

use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\ObjectProxy;
use Psr\Container\ContainerInterface as CI;
use Slim\Collection as SlimCollection;

class Bootstrap
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings(SlimCollection $settings) : array
    {
        $map = [];

        $map['brightwoodTelegramTransport'] = fn (CI $c) =>
            new TelegramTransport(
                $settings['telegram']['brightwood_bot_token']
            );

        $map['cardsRootDeserializer'] = fn (CI $c) =>
            new RootDeserializer(
                new SerializationConfig(
                    $c->telegramUserRepository,
                    $c->storyParser,
                    $c->cases
                ),
                new CardSerializer(),
                new SuitSerializer()
            );

        $map['storyParser'] = fn (CI $c) =>
            new StoryParser();

        $map['storyRepository'] = fn (CI $c) =>
            new StoryRepository();

        $map['storyStatusRepository'] = fn (CI $c) =>
            new StoryStatusRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new StoryStatusHydrator(
                        $c->telegramUserRepository
                    )
                )
            );

        return $map;
    }
}
