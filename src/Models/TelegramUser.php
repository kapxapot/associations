<?php

namespace App\Models;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\NamedInterface;
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
 * @property integer|null $userId
 * @property integer $telegramId
 * @property string|null $username
 * @property string|null $firstName
 * @property string|null $lastName
 * @method User|null user()
 * @method static withUser(User|callable|null $user)
 */
class TelegramUser extends DbModel implements CreatedAtInterface, GenderedInterface, NamedInterface, UpdatedAtInterface
{
    use CreatedAt;
    use Meta;
    use UpdatedAt;

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
        return $this->getMetaValue('bot_admin', false);
    }

    /**
     * @return $this
     */
    public function withBotAdmin(bool $botAdmin): self
    {
        $this->setMetaValue('bot_admin', $botAdmin);

        return $this;
    }

    // GenderedInterface

    public function hasGender(): bool
    {
        return $this->genderId !== null;
    }

    public function gender(): ?int
    {
        return $this->genderId;
    }

    // NamedInterface

    public function name(): string
    {
        return $this->publicName();
    }
}
