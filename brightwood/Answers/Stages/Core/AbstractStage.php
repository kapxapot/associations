<?php

namespace Brightwood\Answers\Stages\Core;

use App\Models\TelegramUser;

abstract class AbstractStage
{
    protected StageContext $context;

    public function __construct(StageContext $context)
    {
        $this->context = $context;
    }

    protected function tgUser(): TelegramUser
    {
        return $this->context->tgUser();
    }

    protected function parse(string $text, ?array $vars = null): string
    {
        return $this->context->parse($text, $vars);
    }
}
