<?php

namespace Brightwood\Models\Messages;

class TextMessage extends Message
{
    public function __construct(?string ...$lines)
    {
        parent::__construct($lines);
    }
}
