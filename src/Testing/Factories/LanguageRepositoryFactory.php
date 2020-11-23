<?php

namespace App\Testing\Factories;

use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Testing\Mocks\Repositories\LanguageRepositoryMock;
use App\Testing\Seeders\LanguageSeeder;

class LanguageRepositoryFactory
{
    public static function make() : LanguageRepositoryInterface
    {
        return new LanguageRepositoryMock(
            new LanguageSeeder()
        );
    }
}
