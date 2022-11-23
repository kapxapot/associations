<?php

namespace App\Generators;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\UserGenerator as BaseUserGenerator;
use Plasticode\Models\Validation\UserValidation;

class UserGenerator extends BaseUserGenerator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        GeneratorContext $context,
        UserRepositoryInterface $userRepository,
        UserValidation $userValidation
    )
    {
        parent::__construct(
            $context,
            $userRepository,
            $userValidation
        );

        $this->userRepository = $userRepository;
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    public function getRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }
}
