<?php

namespace App\Validation\Rules\Generic;

use App\Models\Word;
use Respect\Validation\Rules\AbstractRule;

class AbstractStringNotEqualsWord extends AbstractRule
{
    private Word $wordToCompare;

    public function __construct(
        Word $wordToCompare
    )
    {
        $this->wordToCompare = $wordToCompare;
    }

    public function validate($input)
    {
        return $this->wordToCompare->equalsWordStr($input);
    }
}
