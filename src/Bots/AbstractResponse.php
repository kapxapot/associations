<?php

namespace App\Bots;

abstract class AbstractResponse
{
    public ?string $text;

    public function __construct(?string $text)
    {
        $this->text = $text;
    }
}
