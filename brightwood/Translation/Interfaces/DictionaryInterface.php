<?php

namespace Brightwood\Translation\Interfaces;

interface DictionaryInterface
{
    public function languageCode(): string;

    public function languageName(): string;

    /**
     * @return array<string, string>
     */
    public function definitions(): array;
}
