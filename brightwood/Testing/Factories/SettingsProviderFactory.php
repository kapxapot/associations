<?php

namespace Brightwood\Testing\Factories;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;

class SettingsProviderFactory
{
    public function __invoke(): SettingsProviderInterface
    {
        return new SettingsProvider([
            'telegram' => ['brightwood_bot_token' => 'i am token']
        ]);
    }
}
