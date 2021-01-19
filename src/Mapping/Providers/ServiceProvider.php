<?php

namespace App\Mapping\Providers;

use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
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
use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Services\NewsAggregatorService;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;

class ServiceProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AnniversaryService::class =>
                fn (ContainerInterface $c) => new AnniversaryService(),

            AssociationFeedbackService::class =>
                fn (ContainerInterface $c) => new AssociationFeedbackService(
                    $c->get(AssociationFeedbackRepositoryInterface::class),
                    $c->get(AssociationRepositoryInterface::class),
                    $c->get(ValidatorInterface::class),
                    $c->get(ValidationRules::class)
                ),

            AssociationRecountService::class =>
                fn (ContainerInterface $c) => new AssociationRecountService(
                    $c->get(AssociationRepositoryInterface::class),
                    $c->get(AssociationSpecification::class),
                    $c->get(EventDispatcher::class)
                ),

            associationService => fn (ContainerInterface $c) =>
                new AssociationService(
                    $c->associationRepository
                ),

            casesService => fn (ContainerInterface $c) =>
                new CasesService(
                    $c->cases
                ),

            dictionaryService => fn (ContainerInterface $c) =>
                new DictionaryService(
                    $c->dictWordRepository,
                    $c->externalDictService,
                    $c->get(EventDispatcher::class)
                ),

            externalDictService => fn (ContainerInterface $c) =>
                new YandexDictService(
                    $c->dictWordRepository,
                    $c->yandexDict
                ),

            gameService => fn (ContainerInterface $c) =>
                new GameService(
                    $c->gameRepository,
                    $c->languageService,
                    $c->turnService,
                    $c->wordService
                ),

            languageService => fn (ContainerInterface $c) =>
                new LanguageService(
                    $c->languageRepository,
                    $c->wordRepository,
                    $c->get(SettingsProviderInterface::class),
                    $c->wordService
                ),

            newsAggregatorService => function (ContainerInterface $c) {
                $service = new NewsAggregatorService(
                    $c->linker
                );

                $service->registerStrictSource($c->newsRepository);
                $service->registerSource($c->pageRepository);

                return $service;
            },

            searchService => fn (ContainerInterface $c) =>
                new SearchService(
                    $c->newsRepository,
                    $c->pageRepository,
                    $c->tagRepository,
                    $c->linker
                ),

            tagPartsProviderService => fn (ContainerInterface $c) =>
                new TagPartsProviderService(
                    $c->newsRepository,
                    $c->pageRepository
                ),

            telegramUserService => fn (ContainerInterface $c) =>
                new TelegramUserService(
                    $c->telegramUserRepository,
                    $c->userRepository
                ),

            turnService => fn (ContainerInterface $c) =>
                new TurnService(
                    $c->gameRepository,
                    $c->turnRepository,
                    $c->wordRepository,
                    $c->associationService,
                    $c->get(EventDispatcher::class),
                    $c->logger
                ),

            userService => fn (ContainerInterface $c) =>
                new UserService(
                    $c->config
                ),

            wordFeedbackService => fn (ContainerInterface $c) =>
                new WordFeedbackService(
                    $c->wordFeedbackRepository,
                    $c->wordRepository,
                    $c->validator,
                    $c->validationRules,
                    $c->wordService
                ),

            wordRecountService => fn (ContainerInterface $c) =>
                new WordRecountService(
                    $c->wordSpecification,
                    $c->wordService,
                    $c->get(EventDispatcher::class)
                ),

            wordService => fn (ContainerInterface $c) =>
                new WordService(
                    $c->turnRepository,
                    $c->wordRepository,
                    $c->casesService,
                    $c->validator,
                    $c->validationRules,
                    $c->config,
                    $c->get(EventDispatcher::class)
                ),
        ];
    }
}
