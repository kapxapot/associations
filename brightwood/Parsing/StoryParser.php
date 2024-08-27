<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use App\Models\Interfaces\ActorInterface;
use App\Models\Language;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Plasticode\Semantics\Gender;

class StoryParser
{
    private const DEFAULT_LANGUAGE = Language::RU; // for historical reasons
    private const DEFAULT_GENDER = Gender::MAS;

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
        ?array $vars = null,
        ?string $defaultLangCode = null
    ): string
    {
        $langCode = $actor->languageCode() ?? $defaultLangCode ?? self::DEFAULT_LANGUAGE;
        $gender = $actor->gender() ?? self::DEFAULT_GENDER;

        $translator = ($this->translatorFactory)($langCode);

        return ($this->rendererFactory)()
            ->withTranslator($translator)
            ->withGender($gender)
            ->withVars($vars)
            ->render($text);
    }
}
