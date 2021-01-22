<?php

namespace Brightwood\Mapping\Providers;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Psr\Container\ContainerInterface;

class RepositoryProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            StoryRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryRepository(
                    $c->get(RootDeserializerInterface::class)
                ),

            StoryStatusRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryStatusRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new StoryStatusHydrator(
                            $c->get(TelegramUserRepositoryInterface::class)
                        )
                    )
                ),
        ];
    }
}
