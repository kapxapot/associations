<?php

namespace Brightwood\Config;

use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Serializers\HumanSerializer;
use Brightwood\Serialization\Serializers\PlayerSerializer;
use Brightwood\Serialization\SerializerSource;
use Brightwood\Serialization\UniformDeserializer;
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

        $map['jsonDeserializer'] = fn (CI $c) =>
            new UniformDeserializer(
                new SerializerSource(
                    [
                        Player::class => new PlayerSerializer(),
                        Human::class => new HumanSerializer(
                            $c->telegramUserRepository
                        )
                    ]
                )
            );

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
