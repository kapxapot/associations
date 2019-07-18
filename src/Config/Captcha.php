<?php

namespace App\Config;

class Captcha
{
    public function getReplaces()
    {
        return [
            'а' => ['4'],
            'и' => ['N'],
            'о' => ['0', 'Q'],
            'е' => ['э'],
            'я' => ['R'],
        ];
    }
}
