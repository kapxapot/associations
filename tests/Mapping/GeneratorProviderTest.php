<?php

namespace App\Tests\Mapping;

use App\Generators\AssociationFeedbackGenerator;
use App\Generators\AssociationOverrideGenerator;
use App\Generators\GameGenerator;
use App\Generators\LanguageGenerator;
use App\Generators\NewsGenerator;
use App\Generators\PageGenerator;
use App\Generators\UserGenerator;
use App\Generators\WordFeedbackGenerator;
use App\Generators\WordOverrideGenerator;
use App\Generators\WordRelationTypeGenerator;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Slim\Interfaces\RouterInterface;

final class GeneratorProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            AccessMiddlewareFactory::class,
            ApiInterface::class,
            RouterInterface::class,
            SettingsProviderInterface::class,
            TranslatorInterface::class,
            ValidatorInterface::class,
            Core\ViewInterface::class,

            AssociationFeedbackRepositoryInterface::class,
            AssociationOverrideRepositoryInterface::class,
            GameRepositoryInterface::class,
            LanguageRepositoryInterface::class,
            NewsRepositoryInterface::class,
            PageRepositoryInterface::class,
            CoreRepositories\TagRepositoryInterface::class,
            CoreRepositories\UserRepositoryInterface::class,
            UserRepositoryInterface::class,
            WordFeedbackRepositoryInterface::class,
            WordOverrideRepositoryInterface::class,
            WordRelationTypeRepositoryInterface::class,
        ];
    }

    public function testWiring(): void
    {
        $this->check(AssociationFeedbackGenerator::class);
        $this->check(AssociationOverrideGenerator::class);
        $this->check(GameGenerator::class);
        $this->check(LanguageGenerator::class);
        $this->check(NewsGenerator::class);
        $this->check(PageGenerator::class);
        $this->check(UserGenerator::class);
        $this->check(WordFeedbackGenerator::class);
        $this->check(WordOverrideGenerator::class);
        $this->check(WordRelationTypeGenerator::class);
    }
}
