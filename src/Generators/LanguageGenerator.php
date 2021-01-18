<?php

namespace App\Generators;

use App\Models\Language;
use Plasticode\Generators\Generic\EntityGenerator;

class LanguageGenerator extends EntityGenerator
{
    protected function entityClass(): string
    {
        return Language::class;
    }
}
