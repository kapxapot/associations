<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class TurnExistsException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Turn not found.'
		]
	];
}
