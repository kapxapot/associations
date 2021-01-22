<?php

namespace Brightwood\Mapping\Providers;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Answers\Answerer;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CoreProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            StoryParser::class =>
                fn (ContainerInterface $c) => new StoryParser(),

            Answerer::class =>
                fn (ContainerInterface $c) => new Answerer(
                    $c->get(StoryRepositoryInterface::class),
                    $c->get(StoryStatusRepositoryInterface::class),
                    $c->get(TelegramUserRepositoryInterface::class),
                    $c->get(LoggerInterface::class)
                ),
        ];
    }
}
