<?php

namespace App\Mapping\Providers;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Chunks\Core\Factories\ChunkConfigFactory;
use App\Chunks\Core\Interfaces\ChunkConfigInterface;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Factories\TagsConfigFactory;
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
use App\Models\Validation\Factories\UserValidationFactory;
use App\Parsing\Factories\DoubleBracketsConfigFactory;
use App\Semantics\Association\NaiveAssociationAggregator;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
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
            // core

            AuthInterface::class => Auth::class,
            LinkerInterface::class => Linker::class,

            ConfigCore\TagsConfigInterface::class => TagsConfigFactory::class,
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

            // configs

            ChunkConfigInterface::class => ChunkConfigFactory::class,

            // validation

            UserValidation::class => UserValidationFactory::class,

            // external

            DefinitionSourceInterface::class => DictionaryApi::class,

            // services

            ExternalDictServiceInterface::class => YandexDictService::class,
            NewsAggregatorService::class => NewsAggregatorServiceFactory::class,

            // parsing / rendering

            DoubleBracketsConfig::class => DoubleBracketsConfigFactory::class,

            // semantics

            AssociationAggregatorInterface::class => NaiveAssociationAggregator::class,

            // slim

            NotFoundHandlerInterface::class => NotFoundHandler::class,
        ];
    }
}
