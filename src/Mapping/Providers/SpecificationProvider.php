<?php

namespace App\Mapping\Providers;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class SpecificationProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AssociationSpecification::class =>
                fn (ContainerInterface $c) => new AssociationSpecification(
                    $c->get(AssociationConfigInterface::class)
                ),

            WordSpecification::class =>
                fn (ContainerInterface $c) => new WordSpecification(
                    $c->get(WordConfigInterface::class)
                ),
        ];
    }
}
