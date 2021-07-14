<?php

namespace App\Validation\Rules;

use App\Models\Word;
use App\Services\LanguageService;
use Respect\Validation\Rules\AbstractRule;

class MainWordNonRecursive extends AbstractRule
{
    private LanguageService $languageService;

    private Word $dependentWord;

    public function __construct(
        LanguageService $languageService,
        Word $dependentWord
    )
    {
        $this->languageService = $languageService;

        $this->dependentWord = $dependentWord;
    }

    /**
     * @param string|null $input
     * @return boolean
     */
    public function validate($input)
    {
        $mainWord = $this->languageService->findWord(
            $this->dependentWord->language(),
            $input
        );

        return $mainWord === null
            || !$this->dependentWord->isTransitiveMainOf($mainWord);
    }
}
