<?php

namespace Brightwood\Mapping\Providers;

use App\Repositories\Core\RepositoryContext;
use Brightwood\Hydrators\StoryCandidateHydrator;
use Brightwood\Hydrators\StoryHydrator;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Hydrators\StoryVersionHydrator;
use Brightwood\Repositories\Interfaces\StaticStoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryCandidateRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Repositories\StaticStoryRepository;
use Brightwood\Repositories\StoryCandidateRepository;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Repositories\StoryVersionRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Services\TelegramUserService;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Brightwood\Translation\TranslatorFactory;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class GeneralProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            // repositories

            StoryRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryHydrator::class),
                    $c->get(TelegramUserService::class)
                ),

            StoryCandidateRepositoryInterface::class =>
                fn (ContainerInterface $c) => new StoryCandidateRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, StoryCandidateHydrator::class)
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

            StaticStoryRepositoryInterface::class => StaticStoryRepository::class,

            // cards

            RootDeserializerInterface::class => RootDeserializer::class,

            // translation

            TranslatorFactoryInterface::class => TranslatorFactory::class,
        ];
    }
}
