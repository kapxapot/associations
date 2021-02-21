<?php

namespace App\Models;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\NamedInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer|null $genderId
 * @property integer $id
 * @property int|null $userId
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

    public function privateName(): string
    {
        return $this->firstName ?? $this->lastName ?? $this->username ?? self::noName();
    }

    public function publicName(): string
    {
        return $this->username ?? $this->fullName() ?? self::noName();
    }

    public static function noName(): string
    {
        return 'инкогнито';
    }

    public function fullName(): ?string
    {
        $parts = [$this->firstName, $this->lastName];

        $fullName = implode(' ', array_filter($parts));

        return (strlen($fullName) > 0)
            ? $fullName
            : $this->username;
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
