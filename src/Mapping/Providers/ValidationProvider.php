<?php

namespace App\Mapping\Providers;

use App\Models\Validation\AgeValidation;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;

class ValidationProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AgeValidation::class =>
                fn (ContainerInterface $c) => new AgeValidation(
                    $c->get(ValidationRules::class)
                ),

            UserValidation::class =>
                fn (ContainerInterface $c) => (new UserValidation(
                    $c->get(ValidationRules::class),
                    $c->get(CoreRepositories\UserRepositoryInterface::class)
                ))->extendWith(
                    $c->get(AgeValidation::class)
                ),
        ];
    }
}
