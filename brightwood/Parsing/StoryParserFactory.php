<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;

class StoryParserFactory
{
    private TranslatorFactoryInterface $translatorFactory;

    public function __construct(TranslatorFactoryInterface $translatorFactory)
    {
        $this->translatorFactory = $translatorFactory;
    }

    public function __invoke(): StoryParser
    {
        return new StoryParser(
            new MessageRendererFactory(),
            $this->translatorFactory
        );
    }
}
