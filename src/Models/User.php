<?php

namespace App\Models;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Traits\Gendered;
use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;

/**
 * @property integer $age
 * @method Game|null currentGame()
 * @method bool isMature()
 * @method Game|null lastGame()
 * @method TelegramUser|null telegramUser()
 * @method static withCurrentGame(Game|callable|null $currentGame)
 * @method static withIsMature(bool|callable $mature)
 * @method static withLastGame(Game|callable|null $lastGame)
 * @method static withTelegramUser(TelegramUser|callable|null $telegramUser)
 */
class User extends UserBase implements GenderedInterface
{
    use Gendered;

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            'currentGame',
            'isMature',
            'lastGame',
        ];
    }

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->displayName(),
        ];
    }

    public function hasAge() : bool
    {
        return $this->age > 0;
    }

    public function ageNow() : int
    {
        $yearsPassed = Date::age($this->createdAt)->y;

        return $this->age + $yearsPassed;
    }

    public function displayName() : string
    {
        $name = parent::displayName();

        if (strlen($name) > 0) {
            return $name;
        }

        $tgUser = $this->telegramUser();

        return $tgUser
            ? $tgUser->publicName()
            : 'глюк какой-то';
    }

    public function isTelegramUser() : bool
    {
        return $this->telegramUser() !== null;
    }

    // GenderedInterface

    public function gender() : ?int
    {
        return $this->isTelegramUser()
            ? $this->telegramUser()->gender()
            : null;
    }
}
