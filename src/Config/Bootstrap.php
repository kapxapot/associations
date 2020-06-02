<?php

namespace App\Config;

use App\Auth\Auth;
use App\Core\Linker;
use App\EventHandlers\Association\AssociationApprovedChangedHandler;
use App\EventHandlers\Association\AssociationOutOfDateHandler;
use App\EventHandlers\DictWord\DictWordLinkedHandler;
use App\EventHandlers\DictWord\DictWordUnlinkedHandler;
use App\EventHandlers\Feedback\AssociationFeedbackCreatedHandler;
use App\EventHandlers\Feedback\WordFeedbackCreatedHandler;
use App\EventHandlers\Turn\TurnCreatedHandler;
use App\EventHandlers\Word\WordMatureChangedHandler;
use App\EventHandlers\Word\WordOutOfDateHandler;
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
use App\Hydrators\TurnHydrator;
use App\Hydrators\UserHydrator;
use App\Hydrators\WordFeedbackHydrator;
use App\Hydrators\WordHydrator;
use App\Hydrators\YandexDictWordHydrator;
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\GameRepository;
use App\Repositories\LanguageRepository;
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
use App\Services\TurnService;
use App\Services\UserService;
use App\Services\WordFeedbackService;
use App\Services\WordRecountService;
use App\Services\WordService;
use App\Services\YandexDictService;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Plasticode\Config\Bootstrap as BootstrapBase;
use Plasticode\Events\EventDispatcher;
use Plasticode\ObjectProxy;
use Psr\Container\ContainerInterface as CI;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = parent::getMappings();

        $map['auth'] = fn (CI $c) =>
            new Auth(
                $c->session
            );

        $map['associationFeedbackRepository'] = fn (CI $c) =>
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

        $map['associationRepository'] = fn (CI $c) =>
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

        $map['dictWordRepository'] = fn (CI $c) =>
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

        $map['gameRepository'] = fn (CI $c) =>
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

        $map['languageRepository'] = fn (CI $c) =>
            new LanguageRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new LanguageHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['turnRepository'] = fn (CI $c) =>
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

        $map['userRepository'] = fn (CI $c) =>
            new UserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new UserHydrator(
                        $c->gameRepository,
                        $c->roleRepository,
                        $c->linker,
                        $c->gravatar,
                        $c->userService
                    )
                )
            );

        $map['wordFeedbackRepository'] = fn (CI $c) =>
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

        $map['wordRepository'] = fn (CI $c) =>
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

        $map['localizationConfig'] = fn (CI $c) =>
            new LocalizationConfig();

        $map['linker'] = fn (CI $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->tagsConfig
            );

        $map['config'] = fn (CI $c) =>
            new Config(
                $c->settingsProvider
            );

        $map['captchaConfig'] = fn (CI $c) =>
            new CaptchaConfig();

        $map['associationSpecification'] = fn (CI $c) =>
            new AssociationSpecification(
                $c->config
            );

        $map['wordSpecification'] = fn (CI $c) =>
            new WordSpecification(
                $c->config
            );

        $map['anniversaryService'] = fn (CI $c) =>
            new AnniversaryService();

        $map['associationFeedbackService'] = fn (CI $c) =>
            new AssociationFeedbackService(
                $c->associationFeedbackRepository,
                $c->associationRepository,
                $c->validator,
                $c->validationRules
            );

        $map['associationRecountService'] = fn (CI $c) =>
            new AssociationRecountService(
                $c->associationRepository,
                $c->associationSpecification,
                $c->eventDispatcher
            );

        $map['associationService'] = fn (CI $c) =>
            new AssociationService(
                $c->associationRepository
            );

        $map['casesService'] = fn (CI $c) =>
            new CasesService(
                $c->cases
            );

        $map['dictionaryService'] = fn (CI $c) =>
            new DictionaryService(
                $c->dictWordRepository,
                $c->wordRepository,
                $c->yandexDictService,
                $c->eventDispatcher
            );

        $map['gameService'] = fn (CI $c) =>
            new GameService(
                $c->gameRepository,
                $c->languageService,
                $c->turnService
            );

        $map['languageService'] = fn (CI $c) =>
            new LanguageService(
                $c->languageRepository,
                $c->wordRepository,
                $c->settingsProvider,
                $c->wordService
            );

        $map['turnService'] = fn (CI $c) =>
            new TurnService(
                $c->gameRepository,
                $c->turnRepository,
                $c->wordRepository,
                $c->associationService,
                $c->eventDispatcher
            );

        $map['userService'] = fn (CI $c) =>
            new UserService(
                $c->config
            );

        $map['wordFeedbackService'] = fn (CI $c) =>
            new WordFeedbackService(
                $c->wordFeedbackRepository,
                $c->wordRepository,
                $c->validator,
                $c->validationRules,
                $c->wordService
            );

        $map['wordRecountService'] = fn (CI $c) =>
            new WordRecountService(
                $c->wordRepository,
                $c->wordSpecification,
                $c->eventDispatcher
            );

        $map['wordService'] = fn (CI $c) =>
            new WordService(
                $c->turnRepository,
                $c->wordRepository,
                $c->casesService,
                $c->validator,
                $c->validationRules,
                $c->config
            );

        $map['yandexDictService'] = fn (CI $c) =>
            new YandexDictService(
                $c->yandexDict
            );

        // factories

        $map['loadUncheckedDictWordsJobFactory'] = fn (CI $c) =>
            new LoadUncheckedDictWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->dictionaryService
            );

        $map['matchDanglingDictWordsJobFactory'] = fn (CI $c) =>
            new MatchDanglingDictWordsJobFactory(
                $c->dictWordRepository,
                $c->wordRepository,
                $c->dictionaryService,
                $c->settingsProvider
            );

        $map['updateAssociationsJobFactory'] = fn (CI $c) =>
            new UpdateAssociationsJobFactory(
                $c->associationRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        $map['updateWordsJobFactory'] = fn (CI $c) =>
            new UpdateWordsJobFactory(
                $c->wordRepository,
                $c->settingsProvider,
                $c->eventDispatcher
            );

        // external

        $map['yandexDict'] = fn (CI $c) =>
            new YandexDict(
                $this->settings['yandex_dict']['key']
            );

        // handlers

        $map['notFoundHandler'] = fn (CI $c) =>
            new NotFoundHandler(
                $c
            );

        return $map;
    }

    public function registerEventHandlers(CI $c)
    {
        /** @var EventDispatcher */
        $dispatcher = $c->eventDispatcher;

        $dispatcher->addHandler(
            new AssociationApprovedChangedHandler(
                $c->wordRecountService
            )
        );

        $dispatcher->addHandler(
            new AssociationFeedbackCreatedHandler(
                $c->associationRecountService
            )
        );

        $dispatcher->addHandler(
            new AssociationOutOfDateHandler(
                $c->associationRecountService
            )
        );

        $dispatcher->addHandler(
            new TurnCreatedHandler(
                $c->associationRecountService
            )
        );

        $dispatcher->addHandler(
            new WordFeedbackCreatedHandler(
                $c->wordRecountService
            )
        );

        $dispatcher->addHandler(
            new WordMatureChangedHandler(
                $c->associationRecountService
            )
        );

        $dispatcher->addHandler(
            new WordOutOfDateHandler(
                $c->wordRecountService
            )
        );

        $dispatcher->addHandler(
            new DictWordLinkedHandler(
                $c->wordRecountService
            )
        );

        $dispatcher->addHandler(
            new DictWordUnlinkedHandler(
                $c->wordRecountService
            )
        );
    }
}
