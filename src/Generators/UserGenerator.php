<?php

namespace App\Generators;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\UserGenerator as BaseUserGenerator;
use Plasticode\Validation\Interfaces\ValidationInterface;

class UserGenerator extends BaseUserGenerator
{
    public function __construct(
        GeneratorContext $context,
        UserRepositoryInterface $userRepository,
        ValidationInterface $userValidation
    )
    {
        parent::__construct(
            $context,
            $userRepository,
            $userValidation
        );
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return parent::getRepository();
    }
}
