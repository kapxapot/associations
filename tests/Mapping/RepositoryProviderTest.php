<?php

namespace App\Tests\Mapping;

use App\Mapping\Providers\RepositoryProvider;
use App\Repositories\AliceUserRepository;
use App\Repositories\AssociationFeedbackRepository;
use App\Repositories\AssociationRepository;
use App\Repositories\DefinitionRepository;
use App\Repositories\GameRepository;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
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
use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\DbMetadata;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Testing\AbstractProviderTest;

final class RepositoryProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Access::class,
            AuthInterface::class,
            CacheInterface::class,
            DbMetadata::class,

            CoreRepositories\TagRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new RepositoryProvider();
    }

    public function testWiring(): void
    {
        $this->check(
            AliceUserRepositoryInterface::class,
            AliceUserRepository::class
        );

        $this->check(
            AssociationFeedbackRepositoryInterface::class,
            AssociationFeedbackRepository::class
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
            WordRepositoryInterface::class,
            WordRepository::class
        );
    }
}
