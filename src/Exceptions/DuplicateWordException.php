<?php

namespace App\Exceptions;

use Plasticode\Exceptions\InvalidResultException;

class DuplicateWordException extends InvalidResultException
{
    public string $word;

    public function __construct(string $word)
    {
        parent::__construct('Word is already used in this game.');

        $this->word = $word;
    }
}
