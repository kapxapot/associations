<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use Brightwood\Translation\TranslatorFactory;

class StoryParserFactory
{
    public function __invoke(): StoryParser
    {
        return new StoryParser(
            new MessageRendererFactory(),
            new TranslatorFactory()
        );
    }
}
