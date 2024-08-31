<?php

namespace App\Models;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\UserInterface;
use App\Models\Traits\Gendered;
use App\Policies\UserPolicy;
use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;

/**
 * @property integer $age
 * @method AliceUser|null aliceUser()
 * @method Game|null currentGame()
 * @method bool isMature()
 * @method Game|null lastGame()
 * @method UserPolicy policy()
 * @method SberUser|null sberUser()
 * @method TelegramUser|null telegramUser()
 * @method static withAliceUser(AliceUser|callable|null $aliceUser)
 * @method static withCurrentGame(Game|callable|null $currentGame)
 * @method static withIsMature(bool|callable $mature)
 * @method static withLastGame(Game|callable|null $lastGame)
 * @method static withPolicy(UserPolicy $policy)
 * @method static withSberUser(SberUser|callable|null $sberUser)
 * @method static withTelegramUser(TelegramUser|callable|null $telegramUser)
 */
class User extends UserBase implements GenderedInterface, UserInterface
{
    use Gendered;

    const NONE = null;

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'currentGame',
            'isMature',
            'lastGame',
        ];
    }

    public function hasAge(): bool
    {
        return $this->age > 0;
    }

    public function ageNow(): int
    {
        $yearsPassed = Date::age($this->createdAt)->y;

        return $this->age + $yearsPassed;
    }

    public function displayName(): string
    {
        $name = parent::displayName();

        if (strlen($name) > 0) {
            return $name;
        }

        $tgUser = $this->telegramUser();

        if ($tgUser !== null) {
            return $tgUser->publicName();
        }

        $aliceUser = $this->aliceUser();

        if ($aliceUser !== null) {
            return $aliceUser->name();
        }

        $sberUser = $this->sberUser();

        return $sberUser
            ? $sberUser->name()
            : 'глюк какой-то';
    }

    public function isTelegramUser(): bool
    {
        return $this->telegramUser() !== null;
    }

    public function isAliceUser(): bool
    {
        return $this->aliceUser() !== null;
    }

    public function isSberUser(): bool
    {
        return $this->sberUser() !== null;
    }

    public function isGroup(): bool
    {
        $tgUser = $this->telegramUser();

        return $tgUser && $tgUser->isChat();
    }

    public function isAdmin(): bool
    {
        return $this->role()->tag == 'admin';
    }

    // GenderedInterface

    public function gender(): ?int
    {
        return $this->isTelegramUser()
            ? $this->telegramUser()->gender()
            : null;
    }

    // UserInterface

    public function toUser(): User
    {
        return $this;
    }

    public function toTelegramUser(): ?TelegramUser
    {
        return $this->telegramUser();
    }

    // serialization

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'login' => $this->login,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender(),
            'age' => $this->age,
            'role_id' => $this->roleId,
            'created_at' => $this->createdAtIso(),
            'updated_at' => $this->updatedAtIso(),
            'display_name' => $this->displayName(),
            'is_telegram' => $this->isTelegramUser(),
            'is_alice' => $this->isAliceUser(),
            'is_sber' => $this->isSberUser(),
        ];
    }

    public function serializePublic(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->displayName(),
        ];
    }
}
