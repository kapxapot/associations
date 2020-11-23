<?php

namespace Brightwood\Config;

use Brightwood\Answers\Answerer;
use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\ObjectProxy;
use Slim\Container;

class Bootstrap
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings(array $settings) : array
    {
        $map = [];

        $map['brightwoodTelegramTransport'] = fn (Container $c) =>
            new TelegramTransport(
                $settings['telegram']['brightwood_bot_token']
            );

        $map['cardsRootDeserializer'] = fn (Container $c) =>
            new RootDeserializer(
                new SerializationConfig(
                    $c->telegramUserRepository,
                    $c->storyParser,
                    $c->cases
                ),
                new CardSerializer(),
                new SuitSerializer()
            );

        $map['storyParser'] = fn (Container $c) =>
            new StoryParser();

        $map['storyRepository'] = fn (Container $c) =>
            new StoryRepository(
                $c->cardsRootDeserializer
            );

        $map['storyStatusRepository'] = fn (Container $c) =>
            new StoryStatusRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new StoryStatusHydrator(
                        $c->telegramUserRepository
                    )
                )
            );

        $map['answerer'] = fn (Container $c) =>
            new Answerer(
                $c->storyRepository,
                $c->storyStatusRepository,
                $c->telegramUserRepository,
                $c->logger
            );

        return $map;
    }
}
