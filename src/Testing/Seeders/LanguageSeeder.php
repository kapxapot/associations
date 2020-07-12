<?php

namespace App\Testing\Seeders;

use App\Models\Language;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LanguageSeeder implements ArraySeederInterface
{
    /**
     * @return Language[]
     */
    public function seed() : array
    {
        return [
            new Language(
                [
                    'id' => 1,
                    'name' => 'Dummy',
                ]
            )
        ];
    }
}
