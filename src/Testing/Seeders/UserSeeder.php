<?php

namespace App\Testing\Seeders;

use App\Models\User;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class UserSeeder implements ArraySeederInterface
{
    /**
     * @return User[]
     */
    public function seed() : array
    {
        return [
            new User(
                [
                    'id' => 1
                ]
            )
        ];
    }
}
