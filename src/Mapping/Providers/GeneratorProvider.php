<?php

namespace App\Mapping\Providers;

use App\Core\Serializer;
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
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Psr\Container\ContainerInterface;

class GeneratorProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AssociationFeedbackGenerator::class =>
                fn (ContainerInterface $c) => new AssociationFeedbackGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(AssociationFeedbackRepositoryInterface::class)
                ),

            GameGenerator::class =>
                fn (ContainerInterface $c) => new GameGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(GameRepositoryInterface::class),
                    $c->get(Serializer::class)
                ),

            LanguageGenerator::class =>
                fn (ContainerInterface $c) => new LanguageGenerator(
                    $c->get(GeneratorContext::class)
                ),

            NewsGenerator::class =>
                fn (ContainerInterface $c) => new NewsGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(NewsRepositoryInterface::class),
                    $c->get(TagRepositoryInterface::class)
                ),

            PageGenerator::class =>
                fn (ContainerInterface $c) => new PageGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(PageRepositoryInterface::class),
                    $c->get(TagRepositoryInterface::class)
                ),

            UserGenerator::class =>
                fn (ContainerInterface $c) => new UserGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(UserRepositoryInterface::class),
                    $c->get(UserValidation::class)
                ),

            WordFeedbackGenerator::class =>
                fn (ContainerInterface $c) => new WordFeedbackGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(WordFeedbackRepositoryInterface::class)
                ),
        ];
    }
}
