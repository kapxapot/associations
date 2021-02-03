<?php

namespace Brightwood\Tests\Mapping;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Config\LocalizationConfig;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Linker;
use App\Core\Serializer;
use App\External\YandexDict;
use App\Handlers\NotFoundHandler;
use App\Models\Validation\AgeValidation;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Answers\Answerer;
use Brightwood\Config\SerializationConfig;
use Brightwood\External\TelegramTransport;
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
use Plasticode\Config\Interfaces as ConfigCore;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Data\DbMetadata;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Services\NewsAggregatorService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

final class GeneralProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            // LoggerInterface::class,
            // RouterInterface::class,
            // TranslatorInterface::class,
            // ValidatorInterface::class,
            // ViewInterface::class,
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
