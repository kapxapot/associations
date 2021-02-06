<?php

namespace App\External;

use App\Models\DTO\DefinitionData;

/**
 * Wrapper for api.dictionaryapi.dev - unofficial API for Google Dictionary.
 */
class DictionaryApi implements DefinitionSourceInterface
{
    public function request(string $languageCode, string $word): DefinitionData
    {
        $url = $this->buildUrl($languageCode, $word);
        $result = @file_get_contents($url);

        return new DefinitionData(
            'dictionaryapi.dev',
            $url,
            ($result !== false) ? $result : null
        );
    }

    private function buildUrl(string $languageCode, string $word): string
    {
        return sprintf(
            'https://api.dictionaryapi.dev/api/v2/entries/%s/%s',
            $languageCode,
            urlencode($word)
        );
    }
}
