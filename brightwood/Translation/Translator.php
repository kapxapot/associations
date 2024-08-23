<?php

namespace Brightwood\Translation;

use Brightwood\Translation\Interfaces\TranslatorInterface;
use Plasticode\Util\Arrays;

class Translator implements TranslatorInterface
{
    private ?array $dictionary = null;

    public function __construct(?array $dictionary = null)
    {
        if ($dictionary) {
            $this->dictionary = $dictionary;
        }
    }

    public function translate(string $key): string
    {
        if (empty($this->dictionary)) {
            return $key;
        }

        // look for the exact key
        if (in_array($key, $this->dictionary)) {
            return $this->dictionary[$key];
        }

        // look for the compound key
        $value = Arrays::get($this->dictionary, $key);

        return $value ?? $key;
    }
}
