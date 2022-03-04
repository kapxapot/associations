<?php

namespace App\Tests\Mapping;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Config\LocalizationConfig;
use App\Controllers\AliceBotController;
use App\Controllers\SberBotController;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Linker;
use App\Core\Serializer;
use App\External\DictionaryApi;
use App\External\Interfaces\DefinitionSourceInterface;
use App\External\YandexDict;
use App\Handlers\NotFoundHandler;
use App\Mapping\Providers\GeneralProvider;
use App\Models\Validation\AgeValidation;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\SberUserRepositoryInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Association\NaiveAssociationAggregator;
use App\Services\AliceUserService;
use App\Services\AnniversaryService;
use App\Services\AssociationFeedbackService;
use App\Services\AssociationRecountService;
use App\Services\AssociationService;
use App\Services\CasesService;
use App\Services\DefinitionService;
use App\Services\DictionaryService;
use App\Services\GameService;
use App\Services\Interfaces\ExternalDictServiceInterface;
use App\Services\LanguageService;
use App\Services\SberUserService;
use App\Services\SearchService;
use App\Services\TagPartsProviderService;
use App\Services\TelegramUserService;
use App\Services\TurnService;
use App\Services\UserService;
use App\Services\WordFeedbackService;
use App\Services\WordRecountService;
use App\Services\WordService;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Config\Interfaces as ConfigCore;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
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
            Core\RendererInterface::class,
            Core\SessionInterface::class,
            Core\TranslatorInterface::class,
            Core\ViewInterface::class,

            CoreRepositories\MenuRepositoryInterface::class,
            CoreRepositories\PageRepositoryInterface::class,
            CoreRepositories\TagRepositoryInterface::class,
            CoreRepositories\UserRepositoryInterface::class,

            LoggerInterface::class,
            RouterInterface::class,
            SettingsProviderInterface::class,
            ValidatorInterface::class,

            AliceUserRepositoryInterface::class,
            AssociationFeedbackRepositoryInterface::class,
            AssociationRepositoryInterface::class,
            DefinitionRepositoryInterface::class,
            DictWordRepositoryInterface::class,
            GameRepositoryInterface::class,
            LanguageRepositoryInterface::class,
            NewsRepositoryInterface::class,
            PageRepositoryInterface::class,
            SberUserRepositoryInterface::class,
            TelegramUserRepositoryInterface::class,
            TurnRepositoryInterface::class,
            UserRepositoryInterface::class,
            WordFeedbackRepositoryInterface::class,
            WordRelationRepositoryInterface::class,
            WordRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new GeneralProvider();
    }

    public function testWiring(): void
    {
        $this->check(
            ConfigCore\CaptchaConfigInterface::class,
            CaptchaConfig::class
        );

        $this->check(
            ConfigCore\LocalizationConfigInterface::class,
            LocalizationConfig::class
        );

        $this->check(
            ConfigCore\TagsConfigInterface::class,
            \Plasticode\Config\TagsConfig::class
        );

        $this->check(AuthInterface::class, Auth::class);
        $this->check(Config::class);
        $this->check(LinkerInterface::class, Linker::class);
        $this->check(Serializer::class);

        // aliases

        $this->check(AuthCore\AuthInterface::class, Auth::class);
        $this->check(Core\LinkerInterface::class, Linker::class);

        $this->check(\Plasticode\Config\Config::class, Config::class);
        $this->check(AssociationConfigInterface::class, Config::class);
        $this->check(NewsConfigInterface::class, Config::class);
        $this->check(UserConfigInterface::class, Config::class);
        $this->check(WordConfigInterface::class, Config::class);

        // external

        $this->check(YandexDict::class);
        $this->check(DefinitionSourceInterface::class, DictionaryApi::class);

        // validation

        $this->check(AgeValidation::class);
        $this->check(UserValidation::class);

        // specifications

        $this->check(AssociationSpecification::class);
        $this->check(WordSpecification::class);

        // services

        $this->check(AliceUserService::class);
        $this->check(AnniversaryService::class);
        $this->check(AssociationFeedbackService::class);
        $this->check(AssociationRecountService::class);
        $this->check(AssociationService::class);
        $this->check(CasesService::class);
        $this->check(DefinitionService::class);
        $this->check(DictionaryService::class);
        $this->check(ExternalDictServiceInterface::class);
        $this->check(GameService::class);
        $this->check(LanguageService::class);
        $this->check(NewsAggregatorService::class);
        $this->check(SearchService::class);
        $this->check(SberUserService::class);
        $this->check(TagPartsProviderService::class);
        $this->check(TelegramUserService::class);
        $this->check(TurnService::class);
        $this->check(UserService::class);
        $this->check(WordFeedbackService::class);
        $this->check(WordRecountService::class);
        $this->check(WordService::class);

        // parsing / rendering

        $this->check(DoubleBracketsConfig::class);
        $this->check(NewsLinkMapper::class);
        $this->check(PageLinkMapper::class);
        $this->check(TagLinkMapper::class);

        // semantics

        $this->check(NaiveAssociationAggregator::class);

        // slim

        $this->check(NotFoundHandlerInterface::class, NotFoundHandler::class);

        // controllers

        $this->check(AliceBotController::class);
        $this->check(SberBotController::class);
    }
}
