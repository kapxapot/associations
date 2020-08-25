<?php

namespace Brightwood\Parsing;

use App\Models\TelegramUser;

class StoryParser
{
    public function parseFor(TelegramUser $tgUser, string $text) : string
    {
        return $text;
    }
}
