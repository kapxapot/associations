<?php

namespace Brightwood\Mapping\Providers;

use App\Repositories\Core\RepositoryContext;
use Brightwood\Hydrators\StoryHydrator;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Hydrators\StoryVersionHydrator;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;
use StoryVersionRepository;

class GeneralProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            // repositories

            StoryRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryHydrator::class)
                ),

            StoryStatusRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryStatusRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryStatusHydrator::class)
                ),

            StoryVersionRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryVersionRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryVersionHydrator::class)
                ),

            // cards

            RootDeserializerInterface::class => RootDeserializer::class,
        ];
    }
}
