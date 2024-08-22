<?php

namespace Brightwood\Testing\Factories;

use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Brightwood\Translation\Translator;

class TranslatorTestFactory implements TranslatorFactoryInterface
{
    private array $dictionaryMap = [];

    public function __construct(?array $dictionaryMap = null)
    {
        if ($dictionaryMap) {
            $this->dictionaryMap = $dictionaryMap;
        }
    }

    public function __invoke(string $langCode): TranslatorInterface
    {
        $dictionary = $this->dictionaryMap[$langCode] ?? null;
        return new Translator($dictionary);
    }
}
