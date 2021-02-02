<?php

namespace App\Tests\Mapping;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Mapping\Providers\ServiceProvider;
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
use Plasticode\Core\Interfaces as Core;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Services\NewsAggregatorService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Plasticode\Validation\Interfaces\ValidatorInterface;

final class ServiceProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            AssociationConfigInterface::class,
            UserConfigInterface::class,
            WordConfigInterface::class,

            Core\LinkerInterface::class,
            SettingsProviderInterface::class,
            ValidatorInterface::class,

            AssociationFeedbackRepositoryInterface::class,
            AssociationRepositoryInterface::class,
            DictWordRepositoryInterface::class,
            GameRepositoryInterface::class,
            LanguageRepositoryInterface::class,
            NewsRepositoryInterface::class,
            PageRepositoryInterface::class,
            CoreRepositories\TagRepositoryInterface::class,
            TelegramUserRepositoryInterface::class,
            TurnRepositoryInterface::class,
            UserRepositoryInterface::class,
            WordFeedbackRepositoryInterface::class,
            WordRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new ServiceProvider();
    }

    public function testWiring(): void
    {
        $this->check(AnniversaryService::class);
        $this->check(AssociationFeedbackService::class);
        $this->check(AssociationRecountService::class);
        $this->check(AssociationService::class);
        $this->check(CasesService::class);
        $this->check(DictionaryService::class);
        $this->check(ExternalDictServiceInterface::class);
        $this->check(GameService::class);
        $this->check(LanguageService::class);
        $this->check(NewsAggregatorService::class);
        $this->check(SearchService::class);
        $this->check(TagPartsProviderService::class);
        $this->check(TelegramUserService::class);
        $this->check(TurnService::class);
        $this->check(UserService::class);
        $this->check(WordFeedbackService::class);
        $this->check(WordRecountService::class);
        $this->check(WordService::class);
    }
}
