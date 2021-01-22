<?php

namespace App\Mapping\Providers;

use App\Handlers\NotFoundHandler;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class SlimProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            'notFoundHandler' =>
                fn (ContainerInterface $c) => new NotFoundHandler($c),
        ];
    }
}
