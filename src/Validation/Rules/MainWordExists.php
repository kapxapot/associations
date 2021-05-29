<?php

namespace App\Validation\Rules;

use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use Respect\Validation\Rules\AbstractRule;

class MainWordExists extends AbstractRule
{
    private WordRepositoryInterface $wordRepository;
    private LanguageService $languageService;

    private Language $language;
    private ?Word $dependentWord = null;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService,
        Language $language,
        Word $dependentWord = null
    )
    {
        $this->wordRepository = $wordRepository;
        $this->languageService = $languageService;

        $this->language = $language;
        $this->dependentWord = $dependentWord;
    }

    public function validate($input)
    {
        $mainWordStr = $this->languageService->normalizeWord(
            $this->language,
            $input
        );

        $mainWord = $this
            ->wordRepository
            ->findInLanguage(
                $this->language,
                $mainWordStr
            );
        
        return $mainWord && !$mainWord->equals($this->dependentWord);
    }
}
