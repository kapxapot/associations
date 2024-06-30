<?php

namespace App\Config;

use Plasticode\Config\Interfaces\CaptchaConfigInterface;

class CaptchaConfig implements CaptchaConfigInterface
{
    public function getReplaces(): array
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
