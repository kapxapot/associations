<?php

namespace App\External;

class YandexDict
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function request(string $languageCode, string $word) : ?string
    {
        $url = $this->buildUrl($languageCode, $word);
        $result = @file_get_contents($url);

        return ($result !== false)
            ? $result
            : null;
    }

    private function buildUrl(string $languageCode, string $word) : string
    {
        return 'https://dictionary.yandex.net/api/v1/dicservice.json/lookup?key=' . $this->key . '&lang=' . $languageCode . '&text=' . urlencode($word);
    }
}
