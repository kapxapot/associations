<?php

namespace App\Testing\Factories;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Testing\Mocks\Repositories\UserRepositoryMock;
use App\Testing\Seeders\UserSeeder;

class UserRepositoryFactory
{
    public static function make(): UserRepositoryInterface
    {
        return new UserRepositoryMock(
            new UserSeeder()
        );
    }

    public function __invoke(): UserRepositoryInterface
    {
        return self::make();
    }
}
