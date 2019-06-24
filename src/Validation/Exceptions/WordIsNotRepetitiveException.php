<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordIsNotRepetitiveException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Word is already used in this game.'
		]
	];
}
