<?php

namespace App\Config;

use App\Core\Linker;
use App\External\YandexDict;
use App\Handlers\NotFoundHandler;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Hydrators\AssociationHydrator;
use App\Hydrators\TurnHydrator;
use App\Hydrators\WordFeedbackHydrator;
use App\Hydrators\WordHydrator;
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\GameRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\TurnRepository;
use App\Repositories\WordFeedbackRepository;
use App\Repositories\WordRepository;
use App\Services\AssociationFeedbackService;
use App\Services\AssociationRecountService;
use App\Services\AssociationService;
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
use Psr\Container\ContainerInterface as CI;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = parent::getMappings();

        $map['associationFeedbackRepository'] = fn (CI $c) =>
            new AssociationFeedbackRepository(
                $c->repositoryContext,
                new AssociationFeedbackHydrator(
                    $c->associationRepository,
                    $c->userRepository
                )
            );

        $map['associationRepository'] = fn (CI $c) =>
            new AssociationRepository(
                $c->repositoryContext,
                new AssociationHydrator(
                    $c->associationFeedbackRepository,
                    $c->languageRepository,
                    $c->turnRepository,
                    $c->wordRepository,
                    $c->linker
                )
            );

        $map['gameRepository'] = fn (CI $c) =>
            new GameRepository(
                $c->repositoryContext
            );

        $map['languageRepository'] = fn (CI $c) =>
            new LanguageRepository(
                $c->repositoryContext
            );

        $map['turnRepository'] = fn (CI $c) =>
            new TurnRepository(
                $c->repositoryContext,
                new TurnHydrator(
                    $c->associationRepository,
                    $c->gameRepository,
                    $c->turnRepository,
                    $c->userRepository,
                    $c->wordRepository
                )
            );

        $map['wordFeedbackRepository'] = fn (CI $c) =>
            new WordFeedbackRepository(
                $c->repositoryContext,
                new WordFeedbackHydrator(
                    $c->userRepository,
                    $c->wordRepository
                )
            );

        $map['wordRepository'] = fn (CI $c) =>
            new WordRepository(
                $c->repositoryContext,
                new WordHydrator(
                    $c->aassociationRepository,
                    $c->languageRepository,
                    $c->turnRepository,
                    $c->wordFeedbackRepository,
                    $c->linker
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

        $map['associationFeedbackService'] = fn (CI $c) =>
            new AssociationFeedbackService(
                $c->validator,
                $c->validationRules
            );

        $map['associationRecountService'] = fn (CI $c) =>
            new AssociationRecountService(
                $c->config
            );

        $map['associationService'] = fn (CI $c) =>
            new AssociationService(
                $c
            );

        $map['dictionaryService'] = fn (CI $c) =>
            new DictionaryService(
                $c->yandexDictService
            );

        $map['gameService'] = fn (CI $c) =>
            new GameService(
                $c->config
            );

        $map['languageService'] = fn (CI $c) =>
            new LanguageService(
                $c->settingsProvider,
                $c->wordService
            );

        $map['turnService'] = fn (CI $c) =>
            new TurnService(
                $c->dispatcher,
                $c->associationService
            );

        $map['userService'] = fn (CI $c) =>
            new UserService(
                $c->config
            );

        $map['wordFeedbackService'] = fn (CI $c) =>
            new WordFeedbackService(
                $c->validator,
                $c->validationRules,
                $c->wordService
            );

        $map['wordRecountService'] = fn (CI $c) =>
            new WordRecountService(
                $c->config
            );

        $map['wordService'] = fn (CI $c) =>
            new WordService(
                $c->config,
                $c->validator,
                $c->validationRules,
                $c->wordRepository,
                $c->cases
            );

        $map['yandexDictService'] = fn (CI $c) =>
            new YandexDictService(
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
                $c->appContext
            );

        return $map;
    }
}
