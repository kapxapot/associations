<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\TelegramUser;
use Plasticode\Util\Cases;

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

    public function tgUser() : TelegramUser
    {
        return $this->tgUser;
    }

    public function isBot() : bool
    {
        return false;
    }

    // NamedInterface

    public function name() : string
    {
        $pic = $this->gender == Cases::MAS
            ? '👦'
            : '👧';

        return $pic . ' ' . $this->tgUser->name();
    }

    // GenderedInterface

    public function gender() : int
    {
        return $this->tgUser->gender();
    }
}
