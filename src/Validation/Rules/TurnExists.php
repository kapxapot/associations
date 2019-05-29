<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use App\Models\Turn;

class TurnExists extends AbstractRule
{
	public function validate($input)
	{
		return Turn::get($input) !== null;
	}
}
