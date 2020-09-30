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

        $this->icon = $this->gender() == Cases::MAS
            ? '👦'
            : '👧';
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
        return $this->tgUser->name();
    }

    // GenderedInterface

    public function gender() : int
    {
        return $this->tgUser->gender();
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['telegram_user_id' => $this->tgUser->getId()]
        );
    }
}
