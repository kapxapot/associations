<?php

namespace App\External\Factories;

use App\External\Interfaces\TelegramTransportInterface;
use App\External\TelegramTransport;
use App\Models\DTO\TelegramBotInfo;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class TelegramTransportFactory
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function __invoke(): TelegramTransportInterface
    {
        $token = $this->settingsProvider->get('telegram.bot_token');

        $botInfo = new TelegramBotInfo($token);

        return new TelegramTransport($botInfo);
    }
}
