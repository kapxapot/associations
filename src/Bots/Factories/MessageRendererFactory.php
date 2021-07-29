<?php

namespace App\Bots\Factories;

use App\Bots\MessageRenderer;

class MessageRendererFactory
{
    public function __invoke()
    {
        return new MessageRenderer();
    }
}
