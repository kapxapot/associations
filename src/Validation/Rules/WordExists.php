<?php

namespace App\Validation\Rules;

use App\Models\Word;
use Respect\Validation\Rules\AbstractRule;

class WordExists extends AbstractRule
{
    public function validate($input)
    {
        return Word::get($input) !== null;
    }
}
