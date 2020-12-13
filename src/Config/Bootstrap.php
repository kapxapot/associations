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
use App\Generators\AssociationFeedbackGenerator;
use App\Generators\GameGenerator;
use App\Generators\LanguageGenerator;
use App\Generators\NewsGenerator;
use App\Generators\PageGenerator;
use App\Generators\WordFeedbackGenerator;
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
use Plasticode\Events\EventDispatcher;
use Plasticode\ObjectProxy;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Services\NewsAggregatorService;
use Psr\Container\ContainerInterface;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = parent::getMappings();

        $map['auth'] = fn (ContainerInterface $c) =>
            new Auth(
                $c->session
            );

        $map['associationFeedbacksGenerator'] = fn (ContainerInterface $c) =>
            new AssociationFeedbackGenerator(
                $c->generatorContext,
                $c->associationFeedbackRepository
            );

        $map['gamesGenerator'] = fn (ContainerInterface $c) =>
            new GameGenerator(
                $c->generatorContext,
                $c->gameRepository,
                $c->serializer
            );

        $map['languagesGenerator'] = fn (ContainerInterface $c) =>
            new LanguageGenerator(
                $c->generatorContext
            );

        $map['newsGenerator'] = fn (ContainerInterface $c) =>
            new NewsGenerator(
                $c->generatorContext,
                $c->newsRepository,
                $c->tagRepository
            );

        $map['pagesGenerator'] = fn (ContainerInterface $c) =>
            new PageGenerator(
                $c->generatorContext,
                $c->pageRepository,
                $c->tagRepository
            );

        $map['wordFeedbacksGenerator'] = fn (ContainerInterface $c) =>
            new WordFeedbackGenerator(
                $c->generatorContext,
                $c->wordFeedbackRepository
            );

        $map['associationFeedbackRepository'] = fn (ContainerInterface $c) =>
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

        $map['associationRepository'] = fn (ContainerInterface $c) =>
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

        $map['dictWordRepository'] = fn (ContainerInterface $c) =>
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

        $map['gameRepository'] = fn (ContainerInterface $c) =>
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

        $map['languageRepository'] = fn (ContainerInterface $c) =>
            new LanguageRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new LanguageHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['newsRepository'] = fn (ContainerInterface $c) =>
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

        $map['pageRepository'] = fn (ContainerInterface $c) =>
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

        $map['telegramUserRepository'] = fn (ContainerInterface $c) =>
            new TelegramUserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new TelegramUserHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['turnRepository'] = fn (ContainerInterface $c) =>
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

        $map['userRepository'] = fn (ContainerInterface $c) =>
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

        $map['wordFeedbackRepository'] = fn (ContainerInterface $c) =>
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

        $map['wordRepository'] = fn (ContainerInterface $c) =>
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

        $map['config'] = fn (ContainerInterface $c) =>
            new Config(
                $c->settingsProvider
            );

        $map['captchaConfig'] = fn (ContainerInterface $c) =>
            new CaptchaConfig();

        $map['localizationConfig'] = fn (ContainerInterface $c) =>
            new LocalizationConfig();

        $map['tagsConfig'] = fn (ContainerInterface $c) =>
            new TagsConfig(
                [
                    News::class => 'news',
                    Page::class => 'pages',
                ]
            );

        $map['linker'] = fn (ContainerInterface $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->tagsConfig
            );

        $map['serializer'] = fn (ContainerInterface $c) =>
            new Serializer();

        $map['tagLinkMapper'] = fn (ContainerInterface $c) =>
            new TagLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['pageLinkMapper'] = fn (ContainerInterface $c) =>
            new PageLinkMapper(
                $c->pageRepository,
                $c->tagRepository,
                $c->renderer,
                $c->linker,
                $c->tagLinkMapper
            );

        $map['newsLinkMapper'] = fn (ContainerInterface $c) =>
            new NewsLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['doubleBracketsConfig'] = function (ContainerInterface $c) {
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

        $map['ageValidation'] = fn (ContainerInterface $c) =>
            new AgeValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (ContainerInterface $c) =>
            new UserValidation(
                $c->validationRules,
                $c->ageValidation,
                $c->userRepository
            );

        $map['associationSpecification'] = fn (ContainerInterface $c) =>
            new AssociationSpecification(
                $c->config
            );

        $map['wordSpecification'] = fn (ContainerInterface $c) =>
            new WordSpecification(
                $c->config
            );

        $map['anniversaryService'] = fn (ContainerInterface $c) =>
            new AnniversaryService();

        $map['associationFeedbackService'] = fn (ContainerInterface $c) =>
            new AssociationFeedbackService(
                $c->associationFeedbackRepository,
                $c->associationRepository,
                $c->validator,
                $c->validationRules
            );

        $map['associationRecountService'] = fn (ContainerInterface $c) =>
            new AssociationRecountService(
                $c->associationRepository,
                $c->associationSpecification,
                $c->eventDispatcher
            );

        $map['associationService'] = fn (ContainerInterface $c) =>
            new AssociationService(
                $c->associationRepository
            );

        $map['casesService'] = fn (ContainerInterface $c) =>
            new CasesService(
                $c->cases
            );

        $map['dictionaryService'] = fn (ContainerInterface $c) =>
            new DictionaryService(
                $c->dictWordRepository,
                $c->externalDictService,
                $c->eventDispatcher
            );

        $map['externalDictService'] = fn (ContainerInterface $c) =>
            new YandexDictService(
                $c->dictWordRepository,
                $c->yandexDict
            );

        $map['gameService'] = fn (ContainerInterface $c) =>
            new GameService(
                $c->gameRepository,
                $c->languageService,
                $c->turnService,
                $c->wordService
            );

        $map['languageService'] = fn (ContainerInterface $c) =>
            new LanguageService(
                $c->languageRepository,
                $c->wordRepository,
                $c->settingsProvider,
                $c->wordService
            );

        $map['newsAggregatorService'] = function (ContainerInterface $c) {
            $service = new NewsAggregatorService(
                $c->linker
            );

            $service->registerStrictSource($c->newsRepository);
            $service->registerSource($c->pageRepository);

            return $service;
        };

        $map['searchService'] = fn (ContainerInterface $c) =>
            new SearchService(
                $c->newsRepository,
                $c->pageRepository,
                $c->tagRepository,
                $c->linker
            );

        $map['tagPartsProviderService'] = fn (ContainerInterface $c) =>
            new TagPartsProviderService(
                $c->newsRepository,
                $c->pageRepository
            );

        $map['telegramUserService'] = fn (ContainerInterface $c) =>
            new TelegramUserService(
                $c->telegramUserRepository,
                $c->userRepository
            );

        $map['turnService'] = fn (ContainerInterface $c) =>
            new TurnService(
                $c->gameRepository,
                $c->turnRepository,
                $c->wordRepository,
                $c->associationService,
                $c->eventDispatcher,
                $c->logger
            );

        $map['userService'] = fn (ContainerInterface $c) =>
            new UserService(
                $c->config
            );

        $map['wordFeedbackService'] = fn (ContainerInterface $c) =>
            new WordFeedbackService(
                $c->wordFeedbackRepository,
                $c->wordRepository,
                $c->validator,
                $c->validationRules,
                $c->wordService
            );

        $map['wordRecountService'] = fn (ContainerInterface $c) =>
            new WordRecountService(
                $c->wordSpecification,
                $c->wordService,
                $c->eventDispatcher
            );

        $map['wordService'] = fn (ContainerInterface $c) =>
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

        $map['loadUncheckedDictWordsJobFactory'] = fn (ContainerInterface $c) =>
            new LoadUncheckedDictWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->dictionaryService
            );

        $map['matchDanglingDictWordsJobFactory'] = fn (ContainerInterface $c) =>
            new MatchDanglingDictWordsJobFactory(
                $c->dictWordRepository,
                $c->wordRepository,
                $c->dictionaryService,
                $c->settingsProvider
            );

        $map['updateAssociationsJobFactory'] = fn (ContainerInterface $c) =>
            new UpdateAssociationsJobFactory(
                $c->associationRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        $map['updateWordsJobFactory'] = fn (ContainerInterface $c) =>
            new UpdateWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        // external

        $map['yandexDict'] = fn (ContainerInterface $c) =>
            new YandexDict(
                $this->settings['yandex_dict']['key']
            );

        // handlers

        $map['notFoundHandler'] = fn (ContainerInterface $c) =>
            new NotFoundHandler(
                $c
            );

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

    public function registerEventHandlers(ContainerInterface $c) : void
    {
        /** @var EventDispatcher */
        $dispatcher = $c->eventDispatcher;

        $dispatcher->addHandlers(
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
        );
    }
}
