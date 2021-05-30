<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\TranslatableExceptionInterface;
use App\Exceptions\Traits\TranslatableException;
use Plasticode\Exceptions\InvalidResultException;

abstract class TurnException extends InvalidResultException implements TranslatableExceptionInterface
{
    use TranslatableException;

    public function __construct(string $word)
    {
        $this->addParam($word);

        parent::__construct(
            $this->getRenderedMessage()
        );
    }
}
