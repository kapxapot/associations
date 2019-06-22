<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class GameIsCurrentException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Provided game is finished. Please, reload the page.'
		]
	];
}
