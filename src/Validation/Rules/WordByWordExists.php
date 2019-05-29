<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\Util\Strings;

use App\Models\Language;
use App\Models\Word;

class WordByWordExists extends AbstractRule
{
    private $language;
    private $excludeWord;
    
    public function __construct(Language $language, Word $excludeWord = null)
    {
        $this->language = $language;
        $this->excludeWord = $excludeWord;
    }
    
	public function validate($input)
	{
        $wordWord = Strings::normalize($input);
        $word = Word::findInLanguage($this->language, $wordWord);
	    
		return $word !== null && ($this->excludeWord === null || $word->getId() !== $this->excludeWord->getId());
	}
}
