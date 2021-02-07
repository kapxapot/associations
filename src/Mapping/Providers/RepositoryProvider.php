<?php

namespace App\Mapping\Providers;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Hydrators\AssociationHydrator;
use App\Hydrators\DefinitionHydrator;
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
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\DefinitionRepository;
use App\Repositories\GameRepository;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
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
use App\Repositories\LanguageRepository;
use App\Repositories\NewsRepository;
use App\Repositories\PageRepository;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TurnRepository;
use App\Repositories\UserRepository;
use App\Repositories\WordFeedbackRepository;
use App\Repositories\WordRepository;
use App\Repositories\YandexDictWordRepository;
use App\Services\DictionaryService;
use App\Services\UserService;
use Plasticode\Core\Interfaces as Core;
use Plasticode\External\Gravatar;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\ObjectProxy;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Psr\Container\ContainerInterface;

class RepositoryProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AssociationFeedbackRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AssociationFeedbackRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new AssociationFeedbackHydrator(
                            $c->get(AssociationRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class)
                        )
                    )
                ),

            AssociationRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AssociationRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new AssociationHydrator(
                            $c->get(AssociationFeedbackRepositoryInterface::class),
                            $c->get(LanguageRepositoryInterface::class),
                            $c->get(TurnRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class),
                            $c->get(WordRepositoryInterface::class),
                            $c->get(AuthInterface::class),
                            $c->get(LinkerInterface::class)
                        )
                    )
                ),

            DefinitionRepositoryInterface::class =>
                fn (ContainerInterface $c) => new DefinitionRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new DefinitionHydrator(
                            $c->get(WordRepositoryInterface::class)
                        )
                    )
                ),

            DictWordRepositoryInterface::class =>
                fn (ContainerInterface $c) => new YandexDictWordRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new YandexDictWordHydrator(
                            $c->get(LanguageRepositoryInterface::class),
                            $c->get(WordRepositoryInterface::class)
                        )
                    )
                ),

            GameRepositoryInterface::class =>
                fn (ContainerInterface $c) => new GameRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new GameHydrator(
                            $c->get(LanguageRepositoryInterface::class),
                            $c->get(TurnRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class),
                            $c->get(LinkerInterface::class)
                        )
                    )
                ),

            LanguageRepositoryInterface::class =>
                fn (ContainerInterface $c) => new LanguageRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new LanguageHydrator(
                            $c->get(UserRepositoryInterface::class)
                        )
                    )
                ),

            NewsRepositoryInterface::class =>
                fn (ContainerInterface $c) => new NewsRepository(
                    $c->get(RepositoryContext::class),
                    $c->get(CoreRepositories\TagRepositoryInterface::class),
                    new ObjectProxy(
                        fn () => new NewsHydrator(
                            $c->get(CoreRepositories\UserRepositoryInterface::class),
                            $c->get(CutParser::class),
                            $c->get(Core\LinkerInterface::class),
                            $c->get(ParserInterface::class)
                        )
                    )
                ),

            PageRepositoryInterface::class =>
                fn (ContainerInterface $c) => new PageRepository(
                    $c->get(RepositoryContext::class),
                    $c->get(CoreRepositories\TagRepositoryInterface::class),
                    new ObjectProxy(
                        fn () => new PageHydrator(
                            $c->get(PageRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class),
                            $c->get(CutParser::class),
                            $c->get(Core\LinkerInterface::class),
                            $c->get(ParserInterface::class)
                        )
                    )
                ),

            CoreRepositories\PageRepositoryInterface::class => PageRepositoryInterface::class,

            TelegramUserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TelegramUserRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new TelegramUserHydrator(
                            $c->get(UserRepositoryInterface::class)
                        )
                    )
                ),

            TurnRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TurnRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new TurnHydrator(
                            $c->get(AssociationRepositoryInterface::class),
                            $c->get(GameRepositoryInterface::class),
                            $c->get(TurnRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class),
                            $c->get(WordRepositoryInterface::class)
                        )
                    )
                ),

            UserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new UserRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new UserHydrator(
                            $c->get(GameRepositoryInterface::class),
                            $c->get(CoreRepositories\RoleRepositoryInterface::class),
                            $c->get(TelegramUserRepositoryInterface::class),
                            $c->get(LinkerInterface::class),
                            $c->get(Gravatar::class),
                            $c->get(UserService::class)
                        )
                    )
                ),

            CoreRepositories\UserRepositoryInterface::class => UserRepositoryInterface::class,

            WordFeedbackRepositoryInterface::class =>
                fn (ContainerInterface $c) => new WordFeedbackRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new WordFeedbackHydrator(
                            $c->get(UserRepositoryInterface::class),
                            $c->get(WordRepositoryInterface::class)
                        )
                    )
                ),

            WordRepositoryInterface::class =>
                fn (ContainerInterface $c) => new WordRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new WordHydrator(
                            $c->get(AssociationRepositoryInterface::class),
                            $c->get(DefinitionRepositoryInterface::class),
                            $c->get(LanguageRepositoryInterface::class),
                            $c->get(TurnRepositoryInterface::class),
                            $c->get(UserRepositoryInterface::class),
                            $c->get(WordFeedbackRepositoryInterface::class),
                            $c->get(AuthInterface::class),
                            $c->get(LinkerInterface::class),
                            $c->get(DictionaryService::class)
                        )
                    )
                ),
        ];
    }
}
