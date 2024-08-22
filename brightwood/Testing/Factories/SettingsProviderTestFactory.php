<?php

namespace Brightwood\Testing\Factories;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;

class SettingsProviderTestFactory
{
    public static function make(): SettingsProviderInterface
    {
        return new SettingsProvider([
            'telegram' => ['brightwood_bot_token' => 'i am token']
        ]);
    }

    public function __invoke(): SettingsProviderInterface
    {
        return self::make();
    }
}
