<?php

namespace Brightwood\Tests\Mapping;

use App\Auth\Interfaces\AuthInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Brightwood\Answers\Answerer;
use Brightwood\Config\SerializationConfig;
use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryHydrator;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Hydrators\StoryVersionHydrator;
use Brightwood\Mapping\Providers\GeneralProvider;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryVersionRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Repositories\StoryVersionRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Brightwood\Services\StoryService;
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
        return [
            Access::class,
            AuthInterface::class,
            Core\CacheInterface::class,
            DbMetadata::class,
            LoggerInterface::class,
            SettingsProviderInterface::class,

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
        $this->check(Answerer::class);
        $this->check(StoryParser::class);

        // hydrators

        $this->check(StoryHydrator::class);
        $this->check(StoryStatusHydrator::class);
        $this->check(StoryVersionHydrator::class);

        // repositories

        $this->check(
            StoryRepositoryInterface::class,
            StoryRepository::class
        );

        $this->check(
            StoryStatusRepositoryInterface::class,
            StoryStatusRepository::class
        );

        $this->check(
            StoryVersionRepositoryInterface::class,
            StoryVersionRepository::class
        );

        // stories

        $this->check(WoodStory::class);
        $this->check(EightsStory::class);

        // services

        $this->check(StoryService::class);

        // external

        $this->check(TelegramTransport::class);

        // cards

        $this->check(CardSerializer::class);
        $this->check(RootDeserializerInterface::class, RootDeserializer::class);
        $this->check(SerializationConfig::class);
        $this->check(SuitSerializer::class);
    }
}
