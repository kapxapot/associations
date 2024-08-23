<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use App\Models\Interfaces\ActorInterface;
use App\Models\Language;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Plasticode\Semantics\Gender;

class StoryParser
{
    private MessageRendererFactory $rendererFactory;
    private TranslatorFactoryInterface $translatorFactory;

    public function __construct(
        MessageRendererFactory $rendererFactory,
        TranslatorFactoryInterface $translatorFactory
    )
    {
        $this->rendererFactory = $rendererFactory;
        $this->translatorFactory = $translatorFactory;
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function parse(
        ActorInterface $actor,
        string $text,
        ?array $vars = null
    ): string
    {
        $langCode = $actor->languageCode() ?? Language::RU;
        $gender = $actor->gender() ?? Gender::MAS;

        $translator = ($this->translatorFactory)($langCode);

        return ($this->rendererFactory)()
            ->withTranslator($translator)
            ->withGender($gender)
            ->withVars($vars)
            ->render($text);
    }
}
