<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use App\Models\Interfaces\ActorInterface;
use Brightwood\Models\Data\StoryData;
use Brightwood\Translation\Dictionaries\Ru;
use Brightwood\Translation\TranslatorFactory;
use Plasticode\Semantics\Gender;

class StoryParser
{
    private MessageRendererFactory $rendererFactory;
    private TranslatorFactory $translatorFactory;

    public function __construct(
        MessageRendererFactory $rendererFactory,
        TranslatorFactory $translatorFactory
    )
    {
        $this->rendererFactory = $rendererFactory;
        $this->translatorFactory = $translatorFactory;
    }

    public function parse(
        ActorInterface $actor,
        string $text,
        ?StoryData $data = null
    ): string
    {
        $langCode = $actor->languageCode() ?? Ru::LANG_CODE;
        $gender = $actor->gender() ?? Gender::MAS;

        $renderer = ($this->rendererFactory)();
        $translator = ($this->translatorFactory)($langCode);

        $renderer
            ->withTranslator($translator)
            ->withGender($gender);

        if ($data) {
            $renderer->withVars($data->toArray());
        }

        return $renderer->render($text);
    }
}
