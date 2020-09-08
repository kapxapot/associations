<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\TelegramUser;

class Human extends Player
{
    private TelegramUser $tgUser;

    public function __construct(
        TelegramUser $tgUser
    )
    {
        parent::__construct();

        $this->tgUser = $tgUser;
    }

    public function name() : string
    {
        return $this->tgUser->publicName();
    }

    public function tgUser() : TelegramUser
    {
        return $this->tgUser;
    }

    public function isBot() : bool
    {
        return false;
    }
}
