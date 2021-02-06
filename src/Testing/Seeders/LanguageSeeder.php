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
                    'id' => Language::RUSSIAN,
                    'name' => 'Русский',
                    'yandex_dict_code' => 'ru-ru',
                    'code' => 'ru',
                ]
            )
        ];
    }
}
