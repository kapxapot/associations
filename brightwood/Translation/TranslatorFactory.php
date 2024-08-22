<?php

namespace Brightwood\Translation;

use App\Models\Language;
use Brightwood\Translation\Dictionaries\En;
use Brightwood\Translation\Dictionaries\Ru;
use Brightwood\Translation\Interfaces\DictionaryInterface;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Exception;

class TranslatorFactory implements TranslatorFactoryInterface
{
    public function __invoke(string $langCode): TranslatorInterface
    {
        $dictionary = $this->getDictionary($langCode);
        return new Translator($dictionary);
    }

    /**
     * @throws Exception
     */
    private function getDictionary(string $langCode): ?DictionaryInterface
    {
        switch ($langCode) {
            case Language::RU:
                return new Ru();

            case Language::EN:
                return new En();
        }

        return null;
    }
}
