<?php

namespace App\Auth\Interfaces;

use App\Models\User;
use Plasticode\Auth\Interfaces\AuthInterface as BaseAuthInterface;

interface AuthInterface extends BaseAuthInterface
{
    function getUser() : ?User;
}
