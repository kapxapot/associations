<?php

namespace App\External;

class YandexDictionary
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function request(string $word, string $language = null) : ?string
    {
        $url = $this->buildUrl($word, $language);
        $result = @file_get_contents($url);

        return ($result !== false)
            ? $result
            : null;
    }

    private function buildUrl(string $word, string $language = null) : string
    {
        $language = $language ?? 'ru-ru';

        return 'https://dictionary.yandex.net/api/v1/dicservice.json/lookup?key=' . $this->key . '&lang=' . $language . '&text=' . urlencode($word);
    }
}
