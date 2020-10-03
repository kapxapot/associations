<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\TelegramUser;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

class Human extends Player
{
    private ?TelegramUser $telegramUser;

    public function __construct(
        ?TelegramUser $telegramUser = null
    )
    {
        $this->telegramUser = $telegramUser;

        $this->icon = $this->gender() == Cases::MAS
            ? '👦'
            : '👧';
    }

    public function telegramUser() : TelegramUser
    {
        Assert::notNull($this->telegramUser);

        return $this->telegramUser;
    }

    /**
     * @return static
     */
    public function withTelegramUser(TelegramUser $telegramUser) : self
    {
        $this->telegramUser = $telegramUser;

        return $this;
    }

    public function isBot() : bool
    {
        return false;
    }

    // NamedInterface

    public function name() : string
    {
        return $this->telegramUser()->name();
    }

    // GenderedInterface

    public function gender() : int
    {
        return $this->telegramUser()->gender();
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['telegram_user_id' => $this->telegramUser()->getId()]
        );
    }
}
