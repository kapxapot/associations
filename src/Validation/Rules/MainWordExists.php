<?php

namespace App\Validation\Rules;

use App\Models\Language;
use App\Models\Word;
use Plasticode\Util\Strings;
use Respect\Validation\Rules\AbstractRule;

class MainWordExists extends AbstractRule
{
    private $language;
    private $dependentWord;
    
    public function __construct(Language $language, Word $dependentWord = null)
    {
        $this->language = $language;
        $this->dependentWord = $dependentWord;
    }
    
    public function validate($input)
    {
        $mainWordStr = Strings::normalize($input);
        $mainWord = Word::findInLanguage($this->language, $mainWordStr);
        
        return $mainWord !== null && !$mainWord->equals($this->dependentWord);
    }
}
