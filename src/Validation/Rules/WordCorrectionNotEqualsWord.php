<?php

namespace App\Validation\Rules;

use App\Models\Word;
use Plasticode\Util\Strings;
use Respect\Validation\Rules\AbstractRule;

class WordCorrectionNotEqualsWord extends AbstractRule
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
        $normalized = Strings::normalize($input);

        return $this->wordToCompare->originalWord !== $normalized;
    }
}
