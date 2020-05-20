<?php

namespace App\Models;

use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;

/**
 * @method Game|null currentGame()
 * @method bool isMature()
 * @method Game|null lastGame()
 * @method static withCurrentGame(Game|callable|null $currentGame)
 * @method static withIsMature(bool|callable $mature)
 * @method static withLastGame(Game|callable|null $lastGame)
 */
class User extends UserBase
{
    protected function requiredWiths(): array
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

    public function ageNow() : int
    {
        $yearsPassed = Date::age($this->createdAt)->y;

        return $this->age + $yearsPassed;
    }
}
