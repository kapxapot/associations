<?php

namespace App\Validation\Rules;

use App\Models\Word;
use Plasticode\Util\Strings;
use Respect\Validation\Rules\AbstractRule;

class WordTypoNotEqualsWord extends AbstractRule
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
        $typo = Strings::normalize($input);

        return $typo !== $this->wordToCompare->word;
    }
}
