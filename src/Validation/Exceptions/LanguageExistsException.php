<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class LanguageExistsException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Language not found.'
		]
	];
}
