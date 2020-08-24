<?php

namespace Brightwood\Parsing;

use App\Models\TelegramUser;

class StoryParser
{
    private TelegramUser $tgUser;

    public function __construct(
        TelegramUser $tgUser
    )
    {
        $this->tgUser = $tgUser;
    }

    public function parse(string $text) : string
    {
        return $text;
    }
}
