<?php

namespace App\Config;

use App\Auth\Auth;
use App\Core\Linker;
use App\External\YandexDict;
use App\Handlers\NotFoundHandler;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Hydrators\AssociationHydrator;
use App\Hydrators\GameHydrator;
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
use Plasticode\Config\Bootstrap as BootstrapBase;
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
                $c->repositoryContext
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
                        $c->linker
                    )
                )
            );
        
        $map['yandexDictWordRepository'] = fn (CI $c) =>
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

        $map['localizationConfig'] = fn (CI $c) =>
            new LocalizationConfig();

        $map['linker'] = fn (CI $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router
            );

        $map['config'] = fn (CI $c) =>
            new Config(
                $c->settingsProvider
            );

        $map['captchaConfig'] = fn (CI $c) =>
            new CaptchaConfig();

        $map['eventProcessors'] = fn (CI $c) =>
            [
                $c->wordRecountService,
                $c->associationRecountService,
            ];

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
                $c->config
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
                $c->yandexDictService
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
                $c->dispatcher
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
                $c->config
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
                $c->wordRepository,
                $c->yandexDictWordRepository,
                $c->yandexDict
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
}
