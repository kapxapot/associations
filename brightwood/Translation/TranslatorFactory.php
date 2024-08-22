<?php

namespace Brightwood\Translation;

use Brightwood\Translation\Dictionaries\En;
use Brightwood\Translation\Dictionaries\Ru;
use Brightwood\Translation\Interfaces\DictionaryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Exception;

class TranslatorFactory
{
    public function __invoke(string $langCode): TranslatorInterface
    {
        $dictionary = $this->getDictionary($langCode);
        return new Translator($dictionary);
    }

    /**
     * @throws Exception
     */
    private function getDictionary(string $langCode): DictionaryInterface
    {
        switch ($langCode) {
            case Ru::LANG_CODE:
                return new Ru();

            case En::LANG_CODE:
                return new En();
        }

        throw new Exception("Undefined dictionary: {$langCode}");
    }
}
