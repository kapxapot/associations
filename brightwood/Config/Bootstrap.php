<?php

namespace Brightwood\Config;

use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Plasticode\ObjectProxy;
use Psr\Container\ContainerInterface as CI;

class Bootstrap
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
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

        return $map;
    }
}
