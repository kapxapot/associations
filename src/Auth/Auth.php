<?php

namespace App\Auth;

use App\Auth\Interfaces\AuthInterface;
use App\Models\User;
use Plasticode\Auth\Auth as BaseAuth;

class Auth extends BaseAuth implements AuthInterface
{
    public function getUser(): ?User
    {
        return parent::getUser();
    }
}
