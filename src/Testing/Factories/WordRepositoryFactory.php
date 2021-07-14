<?php

namespace App\Testing\Factories;

use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Testing\Mocks\Repositories\WordRepositoryMock;
use App\Testing\Seeders\WordSeeder;

class WordRepositoryFactory
{
    public static function make(
        ?LanguageRepositoryInterface $languageRepository = null
    ): WordRepositoryInterface
    {
        $languageRepository ??= LanguageRepositoryFactory::make();

        return new WordRepositoryMock(
            new WordSeeder(
                $languageRepository
            )
        );
    }

    public function __invoke(
        LanguageRepositoryInterface $languageRepository
    ): WordRepositoryInterface
    {
        return self::make($languageRepository);
    }
}
