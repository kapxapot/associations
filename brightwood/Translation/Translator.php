<?php

namespace Brightwood\Translation;

use Brightwood\Translation\Interfaces\DictionaryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Plasticode\Util\Arrays;

class Translator implements TranslatorInterface
{
    private ?DictionaryInterface $dictionary = null;

    public function __construct(?DictionaryInterface $dictionary = null)
    {
        if ($dictionary) {
            $this->dictionary = $dictionary;
        }
    }

    public function translate(string $key): string
    {
        if (!$this->dictionary) {
            return $key;
        }

        $value = Arrays::get($this->dictionary->definitions(), $key);

        return $value ?? $key;
    }
}
