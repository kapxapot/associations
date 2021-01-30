<?php

namespace App\Mapping\Providers;

use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\External\YandexDict;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AnniversaryService;
use App\Services\AssociationFeedbackService;
use App\Services\AssociationRecountService;
use App\Services\AssociationService;
use App\Services\CasesService;
use App\Services\DictionaryService;
use App\Services\GameService;
use App\Services\Interfaces\ExternalDictServiceInterface;
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
use Plasticode\Core\Interfaces as Core;
use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Services\NewsAggregatorService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Cases;
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

            AssociationService::class =>
                fn (ContainerInterface $c) => new AssociationService(
                    $c->get(AssociationRepositoryInterface::class)
                ),

            CasesService::class =>
                fn (ContainerInterface $c) => new CasesService(
                    $c->get(Cases::class)
                ),

            DictionaryService::class =>
                fn (ContainerInterface $c) => new DictionaryService(
                    $c->get(DictWordRepositoryInterface::class),
                    $c->get(ExternalDictServiceInterface::class),
                    $c->get(EventDispatcher::class)
                ),

            ExternalDictServiceInterface::class =>
                fn (ContainerInterface $c) => new YandexDictService(
                    $c->get(DictWordRepositoryInterface::class),
                    $c->get(YandexDict::class)
                ),

            GameService::class =>
                fn (ContainerInterface $c) => new GameService(
                    $c->get(GameRepositoryInterface::class),
                    $c->get(LanguageService::class),
                    $c->get(TurnService::class),
                    $c->get(WordService::class)
                ),

            LanguageService::class =>
                fn (ContainerInterface $c) => new LanguageService(
                    $c->get(LanguageRepositoryInterface::class),
                    $c->get(WordRepositoryInterface::class),
                    $c->get(SettingsProviderInterface::class),
                    $c->get(WordService::class)
                ),

            NewsAggregatorService::class =>
                function (ContainerInterface $c) {
                    $service = new NewsAggregatorService(
                        $c->get(Core\LinkerInterface::class)
                    );

                    $service->registerStrictSource(
                        $c->get(NewsRepositoryInterface::class)
                    );

                    $service->registerSource(
                        $c->get(PageRepositoryInterface::class)
                    );

                    return $service;
                },

            SearchService::class =>
                fn (ContainerInterface $c) => new SearchService(
                    $c->get(NewsRepositoryInterface::class),
                    $c->get(PageRepositoryInterface::class),
                    $c->get(TagRepositoryInterface::class),
                    $c->get(Core\LinkerInterface::class)
                ),

            TagPartsProviderService::class =>
                fn (ContainerInterface $c) => new TagPartsProviderService(
                    $c->get(NewsRepositoryInterface::class),
                    $c->get(PageRepositoryInterface::class)
                ),

            TelegramUserService::class =>
                fn (ContainerInterface $c) => new TelegramUserService(
                    $c->get(TelegramUserRepositoryInterface::class),
                    $c->get(UserRepositoryInterface::class)
                ),

            TurnService::class =>
                fn (ContainerInterface $c) => new TurnService(
                    $c->get(GameRepositoryInterface::class),
                    $c->get(TurnRepositoryInterface::class),
                    $c->get(WordRepositoryInterface::class),
                    $c->get(AssociationService::class),
                    $c->get(EventDispatcher::class)
                ),

            UserService::class =>
                fn (ContainerInterface $c) => new UserService(
                    $c->get(UserConfigInterface::class)
                ),

            WordFeedbackService::class =>
                fn (ContainerInterface $c) => new WordFeedbackService(
                    $c->get(WordFeedbackRepositoryInterface::class),
                    $c->get(WordRepositoryInterface::class),
                    $c->get(ValidatorInterface::class),
                    $c->get(ValidationRules::class),
                    $c->get(WordService::class)
                ),

            WordRecountService::class =>
                fn (ContainerInterface $c) => new WordRecountService(
                    $c->get(WordSpecification::class),
                    $c->get(WordService::class),
                    $c->get(EventDispatcher::class)
                ),

            WordService::class =>
                fn (ContainerInterface $c) => new WordService(
                    $c->get(TurnRepositoryInterface::class),
                    $c->get(WordRepositoryInterface::class),
                    $c->get(CasesService::class),
                    $c->get(ValidatorInterface::class),
                    $c->get(ValidationRules::class),
                    $c->get(WordConfigInterface::class),
                    $c->get(EventDispatcher::class)
                ),
        ];
    }
}
