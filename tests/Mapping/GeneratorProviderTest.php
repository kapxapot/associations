<?php

namespace App\Tests\Mapping;

use App\Generators\AssociationFeedbackGenerator;
use App\Generators\GameGenerator;
use App\Generators\LanguageGenerator;
use App\Generators\NewsGenerator;
use App\Generators\PageGenerator;
use App\Generators\UserGenerator;
use App\Generators\WordFeedbackGenerator;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
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
            ValidatorInterface::class,
            ViewInterface::class,

            AssociationFeedbackRepositoryInterface::class,
            GameRepositoryInterface::class,
            NewsRepositoryInterface::class,
            PageRepositoryInterface::class,
            TagRepositoryInterface::class,
            \Plasticode\Repositories\Interfaces\UserRepositoryInterface::class,
            UserRepositoryInterface::class,
            WordFeedbackRepositoryInterface::class,
        ];
    }

    public function testWiring(): void
    {
        $this->check(AssociationFeedbackGenerator::class);
        $this->check(GameGenerator::class);
        $this->check(LanguageGenerator::class);
        $this->check(NewsGenerator::class);
        $this->check(PageGenerator::class);
        $this->check(UserGenerator::class);
        $this->check(WordFeedbackGenerator::class);
    }
}
