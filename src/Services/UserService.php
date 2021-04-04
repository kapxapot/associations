<?php

namespace App\Services;

use App\Config\Interfaces\UserConfigInterface;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\UserPolicyBuilder;

class UserService
{
    private UserConfigInterface $config;
    private UserPolicyBuilder $userPolicyBuilder;

    public function __construct(
        UserConfigInterface $config,
        UserPolicyBuilder $userPolicyBuilder
    )
    {
        $this->config = $config;
        $this->userPolicyBuilder = $userPolicyBuilder;
    }

    public function isMature(User $user): bool
    {
        return $user->ageNow() >= $this->config->userMatureAge();
    }

    public function getUserPolicy(User $user): UserPolicy
    {
        return $this->userPolicyBuilder->buildFor($user);
    }
}
