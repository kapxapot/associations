<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use App\Models\Language;

class LanguageExists extends AbstractRule
{
	public function validate($input)
	{
		return Language::get($input) !== null;
	}
}
