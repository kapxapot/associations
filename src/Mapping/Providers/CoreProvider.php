<?php

namespace App\Mapping\Providers;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class CoreProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AuthInterface::class =>
                fn (ContainerInterface $c) => new Auth(
                    $c->get(SessionInterface::class)
                ),
        ];
    }

    public function getAliases(): array
    {
        return [
            \Plasticode\Auth\Interfaces\AuthInterface::class => AuthInterface::class,
        ];
    }
}
