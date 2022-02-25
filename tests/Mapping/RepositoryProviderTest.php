<?php

namespace App\Tests\Mapping;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
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
use App\Hydrators\WordRelationHydrator;
use App\Hydrators\YandexDictWordHydrator;
use App\Mapping\Providers\RepositoryProvider;
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
use App\Repositories\Interfaces\SberUserRepositoryInterface;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Repositories\LanguageRepository;
use App\Repositories\NewsRepository;
use App\Repositories\PageRepository;
use App\Repositories\SberUserRepository;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TurnRepository;
use App\Repositories\UserRepository;
use App\Repositories\WordFeedbackRepository;
use App\Repositories\WordOverrideRepository;
use App\Repositories\WordRelationRepository;
use App\Repositories\WordRelationTypeRepository;
use App\Repositories\WordRepository;
use App\Repositories\YandexDictWordRepository;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
use App\Services\DictionaryService;
use App\Services\UserService;
use App\Services\WordService;
use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Data\DbMetadata;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Testing\AbstractProviderTest;

final class RepositoryProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Access::class,
            AuthCore\AuthInterface::class,
            AuthInterface::class,
            Core\CacheInterface::class,
            DbMetadata::class,
            Core\LinkerInterface::class,
            LinkerInterface::class,

            CoreRepositories\RoleRepositoryInterface::class,
            CoreRepositories\TagRepositoryInterface::class,

            CutParser::class,
            ParserInterface::class,

            DictionaryService::class,
            UserService::class,
            WordService::class,

            AssociationAggregatorInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new RepositoryProvider();
    }

    public function testWiring(): void
    {
        $this->check(AliceUserHydrator::class);
        $this->check(AssociationFeedbackHydrator::class);
        $this->check(AssociationHydrator::class);
        $this->check(AssociationOverrideHydrator::class);
        $this->check(DefinitionHydrator::class);
        $this->check(GameHydrator::class);
        $this->check(LanguageHydrator::class);
        $this->check(NewsHydrator::class);
        $this->check(PageHydrator::class);
        $this->check(TelegramUserHydrator::class);
        $this->check(TurnHydrator::class);
        $this->check(UserHydrator::class);
        $this->check(WordFeedbackHydrator::class);
        $this->check(WordHydrator::class);
        $this->check(WordOverrideHydrator::class);
        $this->check(WordRelationHydrator::class);
        $this->check(YandexDictWordHydrator::class);

        $this->check(
            AliceUserRepositoryInterface::class,
            AliceUserRepository::class
        );

        $this->check(
            AssociationFeedbackRepositoryInterface::class,
            AssociationFeedbackRepository::class
        );

        $this->check(
            AssociationOverrideRepositoryInterface::class,
            AssociationOverrideRepository::class
        );

        $this->check(
            AssociationRepositoryInterface::class,
            AssociationRepository::class
        );

        $this->check(
            DefinitionRepositoryInterface::class,
            DefinitionRepository::class
        );

        $this->check(
            DictWordRepositoryInterface::class,
            YandexDictWordRepository::class
        );

        $this->check(
            GameRepositoryInterface::class,
            GameRepository::class
        );

        $this->check(
            LanguageRepositoryInterface::class,
            LanguageRepository::class
        );

        $this->check(
            NewsRepositoryInterface::class,
            NewsRepository::class
        );

        $this->check(
            PageRepositoryInterface::class,
            PageRepository::class
        );

        $this->check(
            CoreRepositories\PageRepositoryInterface::class,
            PageRepositoryInterface::class
        );

        $this->check(
            SberUserRepositoryInterface::class,
            SberUserRepository::class
        );

        $this->check(
            TelegramUserRepositoryInterface::class,
            TelegramUserRepository::class
        );

        $this->check(
            TurnRepositoryInterface::class,
            TurnRepository::class
        );

        $this->check(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->check(
            CoreRepositories\UserRepositoryInterface::class,
            UserRepositoryInterface::class
        );

        $this->check(
            WordFeedbackRepositoryInterface::class,
            WordFeedbackRepository::class
        );

        $this->check(
            WordOverrideRepositoryInterface::class,
            WordOverrideRepository::class
        );

        $this->check(
            WordRelationRepositoryInterface::class,
            WordRelationRepository::class
        );

        $this->check(
            WordRelationTypeRepositoryInterface::class,
            WordRelationTypeRepository::class
        );

        $this->check(
            WordRepositoryInterface::class,
            WordRepository::class
        );
    }
}
