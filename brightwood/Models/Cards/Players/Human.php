<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\TelegramUser;
use Plasticode\Semantics\Gender;
use Webmozart\Assert\Assert;

class Human extends Player
{
    private ?TelegramUser $telegramUser;

    public function __construct(
        ?TelegramUser $telegramUser = null
    )
    {
        if ($telegramUser) {
            $this->withTelegramUser($telegramUser);
        }
    }

    public function telegramUser(): TelegramUser
    {
        Assert::notNull($this->telegramUser);

        return $this->telegramUser;
    }

    /**
     * @return $this
     */
    public function withTelegramUser(TelegramUser $telegramUser): self
    {
        $this->telegramUser = $telegramUser;

        if ($this->gender()) {
            $this->icon = $this->gender() == Gender::MAS
                ? '👦'
                : '👧';
        }

        return $this;
    }

    public function isBot(): bool
    {
        return false;
    }

    // NamedInterface

    public function name(): string
    {
        return $this->telegramUser()->name();
    }

    // ActorInterface

    public function gender(): ?int
    {
        return $this->telegramUser()->gender();
    }

    public function languageCode(): ?string
    {
        return $this->telegramUser()->languageCode();
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data): array
    {
        return parent::serialize([
            'telegram_user_id' => $this->telegramUser()->getId()
        ]);
    }
}
