<?php

namespace App\Bots\Factories;

use App\Bots\Interfaces\MessageRendererInterface;
use App\Bots\MessageRenderer;

class MessageRendererFactory
{
    public function __invoke(): MessageRendererInterface
    {
        return new MessageRenderer();
    }
}
