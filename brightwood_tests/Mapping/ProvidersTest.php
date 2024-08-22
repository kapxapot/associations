<?php

namespace Brightwood\Tests\Mapping;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Answers\AnswererFactory;
use Brightwood\Config\SerializationConfig;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Hydrators\StoryCandidateHydrator;
use Brightwood\Hydrators\StoryHydrator;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Hydrators\StoryVersionHydrator;
use Brightwood\Mapping\Providers\GeneralProvider;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Parsing\StoryParser;
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
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Brightwood\Services\StoryService;
use Brightwood\Services\TelegramUserService;
use Brightwood\Testing\Factories\SettingsProviderFactory;
use Brightwood\Translation\TranslatorFactory;
use Plasticode\Auth\Access;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Data\DbMetadata;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Psr\Log\LoggerInterface;

final class GeneralProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        // for TelegramTransportFactory
        $this->container[SettingsProviderInterface::class] = SettingsProviderFactory::class;

        return [
            Access::class,
            AuthInterface::class,
            Core\CacheInterface::class,
            DbMetadata::class,
            LinkerInterface::class,
            LoggerInterface::class,

            TelegramUserRepositoryInterface::class,
            UserRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new GeneralProvider();
    }

    public function testWiring(): void
    {
        $this->check(AnswererFactory::class);
        $this->check(StoryParser::class);

        // hydrators

        $this->check(StoryHydrator::class);
        $this->check(StoryCandidateHydrator::class);
        $this->check(StoryStatusHydrator::class);
        $this->check(StoryVersionHydrator::class);

        // repositories

        $this->check(
            StoryRepositoryInterface::class,
            StoryRepository::class
        );

        $this->check(
            StoryCandidateRepositoryInterface::class,
            StoryCandidateRepository::class
        );

        $this->check(
            StoryStatusRepositoryInterface::class,
            StoryStatusRepository::class
        );

        $this->check(
            StoryVersionRepositoryInterface::class,
            StoryVersionRepository::class
        );

        $this->check(
            StaticStoryRepositoryInterface::class,
            StaticStoryRepository::class
        );

        // stories

        $this->check(WoodStory::class);
        $this->check(EightsStory::class);

        // services

        $this->check(StoryService::class);
        $this->check(TelegramUserService::class);

        // external

        $this->check(TelegramTransportFactory::class);

        // cards

        $this->check(CardSerializer::class);
        $this->check(RootDeserializerInterface::class, RootDeserializer::class);
        $this->check(SerializationConfig::class);
        $this->check(SuitSerializer::class);

        // translator

        $this->check(TranslatorFactory::class);
    }
}
