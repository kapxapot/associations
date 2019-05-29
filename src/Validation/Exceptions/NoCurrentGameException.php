<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NoCurrentGameException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'The game is already on.'
		]
	];
}
