<?php

namespace App\Services;

use App\Config\Interfaces\UserConfigInterface;
use App\Models\User;

class UserService
{
    /** @var UserConfigInterface */
    private $config;

    public function __construct(UserConfigInterface $config)
    {
        $this->config = $config;
    }

    public function isMature(User $user) : bool
    {
        return $user->ageNow() >= $this->config->userMatureAge();
    }
}
