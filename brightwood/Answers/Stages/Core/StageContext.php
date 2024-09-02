<?php

namespace Brightwood\Answers\Stages\Core;

use App\Models\TelegramUser;
use Brightwood\Parsing\StoryParser;

class StageContext
{
    private StoryParser $parser;
    private TelegramUser $tgUser;
    private string $tgLangCode;

    public function __construct(
        StoryParser $parser,
        TelegramUser $tgUser,
        string $tgLangCode
    )
    {
        $this->parser = $parser;
        $this->tgUser = $tgUser;
        $this->tgLangCode = $tgLangCode;
    }

    public function tgUser(): TelegramUser
    {
        return $this->tgUser;
    }

    public function parse(string $text, ?array $vars = null): string
    {
        return $this->parser->parse($this->tgUser, $text, $vars, $this->tgLangCode);
    }
}
