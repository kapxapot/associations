<?php

namespace App\Testing\Seeders;

use App\Models\Language;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LanguageSeeder implements ArraySeederInterface
{
    const RUSSIAN = 1;

    /**
     * @return Language[]
     */
    public function seed(): array
    {
        return [
            new Language([
                'id' => self::RUSSIAN,
                'name' => 'Русский',
                'yandex_dict_code' => 'ru-ru',
                'code' => 'ru',
            ])
        ];
    }
}
