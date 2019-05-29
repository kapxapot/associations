<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use App\Models\Word;

class WordExists extends AbstractRule
{
	public function validate($input)
	{
		return Word::get($input) !== null;
	}
}
