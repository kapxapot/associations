<?php

namespace App\Testing\Seeders;

use App\Models\User;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class UserSeeder implements ArraySeederInterface
{
    const DEFAULT_USER_ID = 1;

    /**
     * @return User[]
     */
    public function seed(): array
    {
        return [
            new User(['id' => self::DEFAULT_USER_ID])
        ];
    }
}
