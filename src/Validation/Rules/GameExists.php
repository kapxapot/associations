<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use App\Models\Game;

class GameExists extends AbstractRule
{
	public function validate($input)
	{
		return Game::get($input) !== null;
	}
}
