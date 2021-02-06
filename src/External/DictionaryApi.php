<?php

namespace App\External;

class DictionaryApi
{
    public function request(string $languageCode, string $word): ?string
    {
        $url = $this->buildUrl($languageCode, $word);
        $result = @file_get_contents($url);

        return ($result !== false)
            ? $result
            : null;
    }

    private function buildUrl(string $languageCode, string $word) : string
    {
        return sprintf(
            'https://api.dictionaryapi.dev/api/v2/entries/%s/%s',
            $languageCode,
            urlencode($word)
        );
    }
}
