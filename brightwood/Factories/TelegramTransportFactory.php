<?php

namespace Brightwood\Factories;

use App\External\Interfaces\TelegramTransportInterface;
use App\External\TelegramTransport;
use App\Models\DTO\TelegramBotInfo;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class TelegramTransportFactory
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(SettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function __invoke(): TelegramTransportInterface
    {
        $token = $this->settingsProvider->get('telegram.brightwood_bot_token');

        if (!$token) {
            throw new InvalidConfigurationException('The Brightwood bot token is undefined.');
        }

        $botInfo = new TelegramBotInfo($token);

        return new TelegramTransport($botInfo);
    }
}
