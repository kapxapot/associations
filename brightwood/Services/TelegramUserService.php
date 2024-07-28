<?php

namespace Brightwood\Services;

use App\Models\TelegramUser;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class TelegramUserService
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function isAdmin(TelegramUser $tgUser): bool
    {
        return $tgUser->telegramId ==
            $this->settingsProvider->get('brightwood.admin_telegram_user_id');
    }
}
