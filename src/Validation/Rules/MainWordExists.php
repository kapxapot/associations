<?php

namespace App\Validation\Rules;

use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Util\Strings;
use Respect\Validation\Rules\AbstractRule;

class MainWordExists extends AbstractRule
{
    private WordRepositoryInterface $wordRepository;
    private Language $language;
    private ?Word $dependentWord = null;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        Language $language,
        Word $dependentWord = null
    )
    {
        $this->wordRepository = $wordRepository;
        $this->language = $language;
        $this->dependentWord = $dependentWord;
    }

    public function validate($input)
    {
        $mainWordStr = Strings::normalize($input);

        $mainWord = $this
            ->wordRepository
            ->findInLanguage(
                $this->language,
                $mainWordStr
            );
        
        return $mainWord && !$mainWord->equals($this->dependentWord);
    }
}
