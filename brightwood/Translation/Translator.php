<?php

namespace Brightwood\Translation;

use Brightwood\Translation\Interfaces\DictionaryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Plasticode\Util\Arrays;

class Translator implements TranslatorInterface
{
    private DictionaryInterface $dictionary;

    public function __construct(DictionaryInterface $dictionary)
    {
        $this->dictionary = $dictionary;
    }

    public function translate(string $key): string
    {
        $value = Arrays::get($this->dictionary->definitions(), $key);

        return $value ?? $key;
    }
}
