<?php

namespace App\Exceptions\Interfaces;

interface RenderableExceptionInterface
{
    public function getRenderedMessage(): string;
}
