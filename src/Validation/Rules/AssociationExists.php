<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use App\Models\Association;

class AssociationExists extends AbstractRule
{
	public function validate($input)
	{
		return Association::get($input) !== null;
	}
}
