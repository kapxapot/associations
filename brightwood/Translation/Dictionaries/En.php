<?php

namespace Brightwood\Translation\Dictionaries;

use Brightwood\Translation\Interfaces\DictionaryInterface;

class En implements DictionaryInterface
{
    const LANG_CODE = 'en';
    const LANG_NAME = 'English';

    public function languageCode(): string
    {
        return self::LANG_CODE;
    }

    public function languageName(): string
    {
        return self::LANG_NAME;
    }

    /**
     * @return array<string, string>
     */
    public function definitions(): array
    {
        return [
        ];
    }
}
