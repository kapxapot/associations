<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class WordIsValid extends AbstractRule
{
    public function validate($input)
    {
        return preg_match('/^([\w\s\-\']+|[\p{Cyrillic}\s\-]+)$/u', $input);
    }
}
