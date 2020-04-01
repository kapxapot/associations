<?php

namespace App\Collections;

use App\Models\Word;

class WordCollection extends LanguageElementCollection
{
    protected string $class = Word::class;
}
