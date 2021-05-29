<?php

namespace App\Validation\Rules;

use App\Models\Word;
use App\Services\LanguageService;
use Respect\Validation\Rules\AbstractRule;

class WordCorrectionNotEqualsWord extends AbstractRule
{
    private LanguageService $languageService;

    private Word $wordToCompare;

    public function __construct(
        LanguageService $languageService,
        Word $wordToCompare
    )
    {
        $this->languageService = $languageService;

        $this->wordToCompare = $wordToCompare;
    }

    public function validate($input)
    {
        $normalized = $this->languageService->normalizeWord(
            $this->wordToCompare->language(),
            $input
        );

        return $this->wordToCompare->originalWord !== $normalized;
    }
}
