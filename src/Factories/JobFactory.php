<?php

namespace App\Factories;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

abstract class JobFactory
{
    protected SettingsProviderInterface $settingsProvider;
    protected EventDispatcher $eventDispatcher;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }
}
