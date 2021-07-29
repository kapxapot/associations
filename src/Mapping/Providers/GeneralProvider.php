<?php

namespace App\Mapping\Providers;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Bots\Interfaces\MessageRendererInterface;
use App\Bots\MessageRenderer;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Config\LocalizationConfig;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Linker;
use App\External\DictionaryApi;
use App\External\Interfaces\DefinitionSourceInterface;
use App\Handlers\NotFoundHandler;
use App\Models\News;
use App\Models\Page;
use App\Models\Validation\Factories\UserValidationFactory;
use App\Parsing\Factories\DoubleBracketsConfigFactory;
use App\Services\Factories\NewsAggregatorServiceFactory;
use App\Services\Interfaces\ExternalDictServiceInterface;
use App\Services\YandexDictService;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Config\Interfaces as ConfigCore;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Services\NewsAggregatorService;

class GeneralProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AuthInterface::class => Auth::class,
            LinkerInterface::class => Linker::class,

            ConfigCore\TagsConfigInterface::class =>
                fn () => new \Plasticode\Config\TagsConfig(
                    [
                        News::class => 'news',
                        Page::class => 'pages',
                    ]
                ),

            ConfigCore\CaptchaConfigInterface::class => CaptchaConfig::class,
            ConfigCore\LocalizationConfigInterface::class => LocalizationConfig::class,

            // aliases

            AuthCore\AuthInterface::class => AuthInterface::class,
            Core\LinkerInterface::class => LinkerInterface::class,

            \Plasticode\Config\Config::class => Config::class,
            AssociationConfigInterface::class => Config::class,
            NewsConfigInterface::class => Config::class,
            UserConfigInterface::class => Config::class,
            WordConfigInterface::class => Config::class,

            // validation

            UserValidation::class => UserValidationFactory::class,

            // external

            DefinitionSourceInterface::class => DictionaryApi::class,

            // services

            ExternalDictServiceInterface::class => YandexDictService::class,
            NewsAggregatorService::class => NewsAggregatorServiceFactory::class,

            // parsing / rendering

            DoubleBracketsConfig::class => DoubleBracketsConfigFactory::class,
            MessageRendererInterface::class => MessageRenderer::class,

            // slim

            NotFoundHandlerInterface::class => NotFoundHandler::class,
        ];
    }
}
