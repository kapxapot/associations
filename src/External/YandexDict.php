<?php

namespace App\External;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class YandexDict
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function request(string $languageCode, string $word): ?string
    {
        $url = $this->buildUrl($languageCode, $word);
        $result = @file_get_contents($url);

        return ($result !== false)
            ? $result
            : null;
    }

    private function buildUrl(string $languageCode, string $word): string
    {
        return
            'https://dictionary.yandex.net/api/v1/dicservice.json/lookup?key='
            . $this->getKey()
            . '&lang=' . $languageCode
            . '&text=' . urlencode($word);
    }

    private function getKey(): string
    {
        return $this->settingsProvider->get('yandex_dict.key');
    }
}
