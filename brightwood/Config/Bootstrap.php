<?php

namespace Brightwood\Config;

use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
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

        $map['brightwoodTelegramTransport'] = fn (CI $c) =>
            new TelegramTransport(
                $settings['telegram']['brightwood_bot_token']
            );

        return $map;
    }
}
