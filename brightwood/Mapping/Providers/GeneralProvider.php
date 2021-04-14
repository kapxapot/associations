<?php

namespace Brightwood\Mapping\Providers;

use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Psr\Container\ContainerInterface;

class GeneralProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            // repositories

            StoryRepositoryInterface::class => StoryRepository::class,

            StoryStatusRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryStatusRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryStatusHydrator::class)
                ),

            // cards

            RootDeserializerInterface::class => RootDeserializer::class,
        ];
    }
}
