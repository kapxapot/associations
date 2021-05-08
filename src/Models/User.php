<?php

namespace App\Models;

use App\Policies\UserPolicy;
use Plasticode\Models\Interfaces\GenderedInterface;
use Plasticode\Models\Traits\Gendered;
use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;

/**
 * @property integer $age
 * @method AliceUser|null aliceUser()
 * @method Game|null currentGame()
 * @method bool isMature()
 * @method Game|null lastGame()
 * @method UserPolicy policy()
 * @method TelegramUser|null telegramUser()
 * @method static withAliceUser(AliceUser|callable|null $aliceUser)
 * @method static withCurrentGame(Game|callable|null $currentGame)
 * @method static withIsMature(bool|callable $mature)
 * @method static withLastGame(Game|callable|null $lastGame)
 * @method static withPolicy(UserPolicy $policy)
 * @method static withTelegramUser(TelegramUser|callable|null $telegramUser)
 */
class User extends UserBase implements GenderedInterface
{
    use Gendered;

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'currentGame',
            'isMature',
            'lastGame',
        ];
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->displayName(),
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

        return $aliceUser
            ? $aliceUser->name()
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

    // GenderedInterface

    public function gender(): ?int
    {
        return $this->isTelegramUser()
            ? $this->telegramUser()->gender()
            : null;
    }
}
