<?php

namespace App\Models;

use App\Models\Interfaces\ActorInterface;
use App\Models\Interfaces\NamedInterface;
use App\Models\Interfaces\UserInterface;
use App\Models\Traits\Actor;
use App\Models\Traits\Meta;
use Exception;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer|null $genderId
 * @property integer $id
 * @property string|null $langCode
 * @property integer|null $userId
 * @property integer $telegramId
 * @property string|null $username
 * @property string|null $firstName
 * @property string|null $lastName
 * @method User|null user()
 * @method static withUser(User|callable|null $user)
 */
class TelegramUser extends DbModel implements CreatedAtInterface, ActorInterface, NamedInterface, UpdatedAtInterface, UserInterface
{
    use Actor;
    use CreatedAt;
    use Meta;
    use UpdatedAt;

    const META_BOT_ADMIN = 'bot_admin';

    private bool $dirty = false;

    protected function requiredWiths(): array
    {
        return ['user'];
    }

    public function isValid(): bool
    {
        return $this->user() !== null;
    }

    public function isNew(): bool
    {
        return $this->isValid() && $this->user()->lastGame() === null;
    }

    /**
     * Is this a fake user made for chat?
     *
     * Telegram chats have negative ids.
     */
    public function isChat(): bool
    {
        return $this->telegramId < 0;
    }

    public function privateName(): string
    {
        return $this->firstName ?? $this->lastName ?? $this->username ?? $this->noName();
    }

    public function publicName(): string
    {
        return $this->username ?? $this->fullName() ?? $this->noName();
    }

    public function noName(): string
    {
        $name = 'инкогнито';

        if ($this->isChat()) {
            $name .= ' чат';
        }

        $name .= ' ' . $this->getId();

        return $name;
    }

    public function fullName(): ?string
    {
        $parts = [$this->firstName, $this->lastName];

        $fullName = implode(' ', array_filter($parts));

        return (strlen($fullName) > 0)
            ? $fullName
            : $this->username;
    }

    public function lastWord(): ?string
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Is the bot chat administrator for this user's chat?
     *
     * Relevant only for group chats (see {@see isChat()}).
     */
    public function isBotAdmin(): bool
    {
        return $this->getMetaValue(self::META_BOT_ADMIN, false);
    }

    /**
     * @return $this
     */
    public function withBotAdmin(bool $botAdmin): self
    {
        if ($this->isBotAdmin() !== $botAdmin) {
            $this->setMetaValue(self::META_BOT_ADMIN, $botAdmin ?: null);
            $this->dirty = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withGenderId(int $genderId): self
    {
        if ($this->genderId !== $genderId) {
            $this->genderId = $genderId;
            $this->dirty = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withLangCode(string $langCode): self
    {
        if ($this->langCode !== $langCode) {
            $this->langCode = $langCode;
            $this->dirty = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $needUpdate = false;

        foreach ($meta as $key => $value) {
            if ($this->getMetaValue($key) !== $value) {
                $needUpdate = true;
                break;
            }
        }

        if ($needUpdate) {
            $this->setMeta($meta);
            $this->dirty = true;
        }

        return $this;
    }

    public function setDirty(bool $dirty = true): void
    {
        $this->dirty = $dirty;
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    // ActorInterface (see Actor for the rest)

    public function gender(): ?int
    {
        return $this->genderId;
    }

    public function languageCode(): ?string
    {
        return $this->langCode;
    }

    // NamedInterface

    public function name(): string
    {
        return $this->publicName();
    }

    // UserInterface

    public function toUser(): ?User
    {
        return $this->user();
    }

    public function toTelegramUser(): TelegramUser
    {
        return $this;
    }
}
