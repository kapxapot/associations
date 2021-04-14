<?php

namespace App\Mapping\Providers;

use App\Hydrators\AliceUserHydrator;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Hydrators\AssociationHydrator;
use App\Hydrators\AssociationOverrideHydrator;
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
use App\Hydrators\WordOverrideHydrator;
use App\Hydrators\YandexDictWordHydrator;
use App\Repositories\AliceUserRepository;
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationOverrideRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\DefinitionRepository;
use App\Repositories\GameRepository;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
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
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Repositories\LanguageRepository;
use App\Repositories\NewsRepository;
use App\Repositories\PageRepository;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TurnRepository;
use App\Repositories\UserRepository;
use App\Repositories\WordFeedbackRepository;
use App\Repositories\WordOverrideRepository;
use App\Repositories\WordRepository;
use App\Repositories\YandexDictWordRepository;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Psr\Container\ContainerInterface;

class RepositoryProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AliceUserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AliceUserRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, AliceUserHydrator::class)
                ),

            AssociationFeedbackRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AssociationFeedbackRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, AssociationFeedbackHydrator::class)
                ),

            AssociationOverrideRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AssociationOverrideRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, AssociationOverrideHydrator::class)
                ),

            AssociationRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AssociationRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, AssociationHydrator::class)
                ),

            DefinitionRepositoryInterface::class =>
                fn (ContainerInterface $c) => new DefinitionRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, DefinitionHydrator::class)
                ),

            DictWordRepositoryInterface::class =>
                fn (ContainerInterface $c) => new YandexDictWordRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, YandexDictWordHydrator::class)
                ),

            GameRepositoryInterface::class =>
                fn (ContainerInterface $c) => new GameRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, GameHydrator::class)
                ),

            LanguageRepositoryInterface::class =>
                fn (ContainerInterface $c) => new LanguageRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, LanguageHydrator::class)
                ),

            NewsRepositoryInterface::class =>
                fn (ContainerInterface $c) => new NewsRepository(
                    $c->get(RepositoryContext::class),
                    $c->get(CoreRepositories\TagRepositoryInterface::class),
                    $this->proxy($c, NewsHydrator::class)
                ),

            PageRepositoryInterface::class =>
                fn (ContainerInterface $c) => new PageRepository(
                    $c->get(RepositoryContext::class),
                    $c->get(CoreRepositories\TagRepositoryInterface::class),
                    $this->proxy($c, PageHydrator::class)
                ),

            CoreRepositories\PageRepositoryInterface::class => PageRepositoryInterface::class,

            TelegramUserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TelegramUserRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, TelegramUserHydrator::class)
                ),

            TurnRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TurnRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, TurnHydrator::class)
                ),

            UserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new UserRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, UserHydrator::class)
                ),

            CoreRepositories\UserRepositoryInterface::class => UserRepositoryInterface::class,

            WordFeedbackRepositoryInterface::class =>
                fn (ContainerInterface $c) => new WordFeedbackRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, WordFeedbackHydrator::class)
                ),

            WordOverrideRepositoryInterface::class =>
                fn (ContainerInterface $c) => new WordOverrideRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, WordOverrideHydrator::class)
                ),

            WordRepositoryInterface::class =>
                fn (ContainerInterface $c) => new WordRepository(
                    $c->get(RepositoryContext::class),
                    $this->proxy($c, WordHydrator::class)
                ),
        ];
    }
}
