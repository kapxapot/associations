<?php

namespace App\Config;

use App\Auth\Auth;
use App\Core\Linker;
use App\Core\Serializer;
use App\EventHandlers\Association\AssociationApprovedChangedHandler;
use App\EventHandlers\Association\AssociationOutOfDateHandler;
use App\EventHandlers\DictWord\DictWordLinkedHandler;
use App\EventHandlers\DictWord\DictWordUnlinkedHandler;
use App\EventHandlers\Feedback\AssociationFeedbackCreatedHandler;
use App\EventHandlers\Feedback\WordFeedbackCreatedHandler;
use App\EventHandlers\Turn\TurnCreatedHandler;
use App\EventHandlers\Word\WordCreatedHandler;
use App\EventHandlers\Word\WordMatureChangedHandler;
use App\EventHandlers\Word\WordOutOfDateHandler;
use App\EventHandlers\Word\WordUpdatedHandler;
use App\External\YandexDict;
use App\Factories\LoadUncheckedDictWordsJobFactory;
use App\Factories\MatchDanglingDictWordsJobFactory;
use App\Factories\UpdateAssociationsJobFactory;
use App\Factories\UpdateWordsJobFactory;
use App\Handlers\NotFoundHandler;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Hydrators\AssociationHydrator;
use App\Hydrators\GameHydrator;
use App\Hydrators\LanguageHydrator;
use App\Hydrators\NewsHydrator;
use App\Hydrators\PageHydrator;
use App\Hydrators\TelegramUserHydrator;
use App\Hydrators\TurnHydrator;
use App\Hydrators\UserHydrator;
use App\Hydrators\WordFeedbackHydrator;
use App\Hydrators\WordHydrator;
use App\Hydrators\YandexDictWordHydrator;
use App\Models\News;
use App\Models\Page;
use App\Models\Validation\AgeValidation;
use App\Models\Validation\UserValidation;
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\GameRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\NewsRepository;
use App\Repositories\PageRepository;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TurnRepository;
use App\Repositories\UserRepository;
use App\Repositories\WordFeedbackRepository;
use App\Repositories\WordRepository;
use App\Repositories\YandexDictWordRepository;
use App\Services\AnniversaryService;
use App\Services\AssociationFeedbackService;
use App\Services\AssociationRecountService;
use App\Services\AssociationService;
use App\Services\CasesService;
use App\Services\DictionaryService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\SearchService;
use App\Services\TagPartsProviderService;
use App\Services\TelegramUserService;
use App\Services\TurnService;
use App\Services\UserService;
use App\Services\WordFeedbackService;
use App\Services\WordRecountService;
use App\Services\WordService;
use App\Services\YandexDictService;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Brightwood\Config\Bootstrap as BrightwoodBootstrap;
use Plasticode\Config\Bootstrap as BootstrapBase;
use Plasticode\Config\TagsConfig;
use Plasticode\ObjectProxy;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Services\NewsAggregatorService;
use Slim\Container;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = parent::getMappings();

        $map['auth'] = fn (Container $c) =>
            new Auth(
                $c->session
            );

        $map['associationFeedbackRepository'] = fn (Container $c) =>
            new AssociationFeedbackRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new AssociationFeedbackHydrator(
                        $c->associationRepository,
                        $c->userRepository
                    )
                )
            );

        $map['associationRepository'] = fn (Container $c) =>
            new AssociationRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new AssociationHydrator(
                        $c->associationFeedbackRepository,
                        $c->languageRepository,
                        $c->turnRepository,
                        $c->userRepository,
                        $c->wordRepository,
                        $c->auth,
                        $c->linker
                    )
                )
            );

        $map['dictWordRepository'] = fn (Container $c) =>
            new YandexDictWordRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new YandexDictWordHydrator(
                        $c->languageRepository,
                        $c->wordRepository
                    )
                )
            );

        $map['gameRepository'] = fn (Container $c) =>
            new GameRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new GameHydrator(
                        $c->languageRepository,
                        $c->turnRepository,
                        $c->userRepository,
                        $c->linker
                    )
                )
            );

        $map['languageRepository'] = fn (Container $c) =>
            new LanguageRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new LanguageHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['newsRepository'] = fn (Container $c) =>
            new NewsRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new NewsHydrator(
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['pageRepository'] = fn (Container $c) =>
            new PageRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new PageHydrator(
                        $c->pageRepository,
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['telegramUserRepository'] = fn (Container $c) =>
            new TelegramUserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new TelegramUserHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['turnRepository'] = fn (Container $c) =>
            new TurnRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new TurnHydrator(
                        $c->associationRepository,
                        $c->gameRepository,
                        $c->turnRepository,
                        $c->userRepository,
                        $c->wordRepository
                    )
                )
            );

        $map['userRepository'] = fn (Container $c) =>
            new UserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new UserHydrator(
                        $c->gameRepository,
                        $c->roleRepository,
                        $c->telegramUserRepository,
                        $c->linker,
                        $c->gravatar,
                        $c->userService
                    )
                )
            );

        $map['wordFeedbackRepository'] = fn (Container $c) =>
            new WordFeedbackRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new WordFeedbackHydrator(
                        $c->userRepository,
                        $c->wordRepository
                    )
                )
            );

        $map['wordRepository'] = fn (Container $c) =>
            new WordRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new WordHydrator(
                        $c->associationRepository,
                        $c->languageRepository,
                        $c->turnRepository,
                        $c->userRepository,
                        $c->wordFeedbackRepository,
                        $c->auth,
                        $c->linker,
                        $c->dictionaryService
                    )
                )
            );

        $map['config'] = fn (Container $c) =>
            new Config(
                $c->settingsProvider
            );

        $map['captchaConfig'] = fn (Container $c) =>
            new CaptchaConfig();

        $map['localizationConfig'] = fn (Container $c) =>
            new LocalizationConfig();

        $map['tagsConfig'] = fn (Container $c) =>
            new TagsConfig(
                [
                    News::class => 'news',
                    Page::class => 'pages',
                ]
            );

        $map['linker'] = fn (Container $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->tagsConfig
            );

        $map['serializer'] = fn (Container $c) =>
            new Serializer(
                $c->linker
            );

        $map['tagLinkMapper'] = fn (Container $c) =>
            new TagLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['pageLinkMapper'] = fn (Container $c) =>
            new PageLinkMapper(
                $c->pageRepository,
                $c->tagRepository,
                $c->renderer,
                $c->linker,
                $c->tagLinkMapper
            );

        $map['newsLinkMapper'] = fn (Container $c) =>
            new NewsLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['doubleBracketsConfig'] = function (Container $c) {
            $config = new LinkMapperSource();

            $config->setDefaultMapper($c->pageLinkMapper);
            
            $config->registerTaggedMappers(
                [
                    $c->newsLinkMapper,
                    $c->tagLinkMapper,
                ]
            );

            return $config;
        };

        $map['ageValidation'] = fn (Container $c) =>
            new AgeValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (Container $c) =>
            new UserValidation(
                $c->validationRules,
                $c->ageValidation,
                $c->userRepository
            );

        $map['associationSpecification'] = fn (Container $c) =>
            new AssociationSpecification(
                $c->config
            );

        $map['wordSpecification'] = fn (Container $c) =>
            new WordSpecification(
                $c->config
            );

        $map['anniversaryService'] = fn (Container $c) =>
            new AnniversaryService();

        $map['associationFeedbackService'] = fn (Container $c) =>
            new AssociationFeedbackService(
                $c->associationFeedbackRepository,
                $c->associationRepository,
                $c->validator,
                $c->validationRules
            );

        $map['associationRecountService'] = fn (Container $c) =>
            new AssociationRecountService(
                $c->associationRepository,
                $c->associationSpecification,
                $c->eventDispatcher
            );

        $map['associationService'] = fn (Container $c) =>
            new AssociationService(
                $c->associationRepository
            );

        $map['casesService'] = fn (Container $c) =>
            new CasesService(
                $c->cases
            );

        $map['dictionaryService'] = fn (Container $c) =>
            new DictionaryService(
                $c->dictWordRepository,
                $c->externalDictService,
                $c->eventDispatcher
            );

        $map['externalDictService'] = fn (Container $c) =>
            new YandexDictService(
                $c->dictWordRepository,
                $c->yandexDict
            );

        $map['gameService'] = fn (Container $c) =>
            new GameService(
                $c->gameRepository,
                $c->languageService,
                $c->turnService,
                $c->wordService
            );

        $map['languageService'] = fn (Container $c) =>
            new LanguageService(
                $c->languageRepository,
                $c->wordRepository,
                $c->settingsProvider,
                $c->wordService
            );

        $map['newsAggregatorService'] = function (Container $c) {
            $service = new NewsAggregatorService(
                $c->linker
            );

            $service->registerStrictSource($c->newsRepository);
            $service->registerSource($c->pageRepository);

            return $service;
        };

        $map['searchService'] = fn (Container $c) =>
            new SearchService(
                $c->newsRepository,
                $c->pageRepository,
                $c->tagRepository,
                $c->linker
            );

        $map['tagPartsProviderService'] = fn (Container $c) =>
            new TagPartsProviderService(
                $c->newsRepository,
                $c->pageRepository
            );

        $map['telegramUserService'] = fn (Container $c) =>
            new TelegramUserService(
                $c->telegramUserRepository,
                $c->userRepository
            );

        $map['turnService'] = fn (Container $c) =>
            new TurnService(
                $c->gameRepository,
                $c->turnRepository,
                $c->wordRepository,
                $c->associationService,
                $c->eventDispatcher
            );

        $map['userService'] = fn (Container $c) =>
            new UserService(
                $c->config
            );

        $map['wordFeedbackService'] = fn (Container $c) =>
            new WordFeedbackService(
                $c->wordFeedbackRepository,
                $c->wordRepository,
                $c->validator,
                $c->validationRules,
                $c->wordService
            );

        $map['wordRecountService'] = fn (Container $c) =>
            new WordRecountService(
                $c->wordSpecification,
                $c->wordService,
                $c->eventDispatcher
            );

        $map['wordService'] = fn (Container $c) =>
            new WordService(
                $c->turnRepository,
                $c->wordRepository,
                $c->casesService,
                $c->validator,
                $c->validationRules,
                $c->config,
                $c->eventDispatcher
            );

        // factories

        $map['loadUncheckedDictWordsJobFactory'] = fn (Container $c) =>
            new LoadUncheckedDictWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->dictionaryService
            );

        $map['matchDanglingDictWordsJobFactory'] = fn (Container $c) =>
            new MatchDanglingDictWordsJobFactory(
                $c->dictWordRepository,
                $c->wordRepository,
                $c->dictionaryService,
                $c->settingsProvider
            );

        $map['updateAssociationsJobFactory'] = fn (Container $c) =>
            new UpdateAssociationsJobFactory(
                $c->associationRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        $map['updateWordsJobFactory'] = fn (Container $c) =>
            new UpdateWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        // external

        $map['yandexDict'] = fn (Container $c) =>
            new YandexDict(
                $this->settings['yandex_dict']['key']
            );

        // handlers

        $map['notFoundHandler'] = fn (Container $c) =>
            new NotFoundHandler(
                $c
            );

        // event handlers

        $map['eventHandlers'] = fn (Container $c) => [
            new AssociationApprovedChangedHandler($c->wordRecountService),
            new AssociationFeedbackCreatedHandler($c->associationRecountService),
            new AssociationOutOfDateHandler($c->associationRecountService),
            new DictWordLinkedHandler($c->wordRecountService),
            new DictWordUnlinkedHandler($c->wordRecountService),
            new TurnCreatedHandler($c->associationRecountService),
            new WordCreatedHandler($c->dictionaryService),
            new WordFeedbackCreatedHandler($c->wordRecountService),
            new WordMatureChangedHandler($c->associationRecountService),
            new WordOutOfDateHandler($c->wordRecountService),
            new WordUpdatedHandler($c->dictionaryService),
        ];

        // Brightwood

        $map = $this->addBrightwood($map);

        return $map;
    }

    private function addBrightwood(array $map) : array
    {
        $brightwoodBootstrap = new BrightwoodBootstrap();

        return array_merge(
            $map,
            $brightwoodBootstrap->getMappings($this->settings)
        );
    }
}
