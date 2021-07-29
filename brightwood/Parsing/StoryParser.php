<?php

namespace Brightwood\Parsing;

use App\Bots\Factories\MessageRendererFactory;
use App\Models\Interfaces\GenderedInterface;
use Brightwood\Models\Data\StoryData;
use Plasticode\Semantics\Gender;

class StoryParser
{
    private MessageRendererFactory $rendererFactory;

    public function __construct(
        MessageRendererFactory $rendererFactory
    )
    {
        $this->rendererFactory = $rendererFactory;
    }

    public function parse(
        GenderedInterface $gendered,
        string $text,
        ?StoryData $data = null
    ): string
    {
        return ($this->rendererFactory)()
            ->withGender($gendered->gender() ?? Gender::MAS)
            ->withVars($data ? $data->toArray() : [])
            ->render($text);
    }
}
