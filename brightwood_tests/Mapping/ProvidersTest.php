<?php

namespace Brightwood\Tests\Mapping;

use Brightwood\Answers\Answerer;
use Brightwood\Config\SerializationConfig;
use Brightwood\External\TelegramTransport;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Mapping\Providers\GeneralProvider;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Repositories\StoryStatusRepository;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\Serialization\Cards\RootDeserializer;
use Brightwood\Serialization\Cards\Serializers\CardSerializer;
use Brightwood\Serialization\Cards\Serializers\SuitSerializer;
use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Data\DbMetadata;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Psr\Log\LoggerInterface;

final class GeneralProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Access::class,
            AuthCore\AuthInterface::class,
            Core\CacheInterface::class,
            DbMetadata::class,
            LoggerInterface::class,
            SettingsProviderInterface::class,

            TelegramUserRepositoryInterface::class,
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

        $this->check(StoryStatusHydrator::class);

        // repositories

        $this->check(StoryRepositoryInterface::class, StoryRepository::class);

        $this->check(
            StoryStatusRepositoryInterface::class,
            StoryStatusRepository::class
        );

        // external

        $this->check(TelegramTransport::class);

        // cards

        $this->check(CardSerializer::class);
        $this->check(RootDeserializerInterface::class, RootDeserializer::class);
        $this->check(SerializationConfig::class);
        $this->check(SuitSerializer::class);
    }
}
