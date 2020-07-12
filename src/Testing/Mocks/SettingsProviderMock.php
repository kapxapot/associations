<?php

namespace App\Testing\Mocks;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProviderMock implements SettingsProviderInterface
{
    private $settings = [
    ];

    public function get(string $path = null, $default = null)
    {
        return Arrays::get($this->settings, $path) ?? $default;
    }
}
