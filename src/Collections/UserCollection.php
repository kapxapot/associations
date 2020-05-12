<?php

namespace App\Collections;

use App\Models\User;
use Plasticode\Collections\UserCollection as BaseUserCollection;

class UserCollection extends BaseUserCollection
{
    protected string $class = User::class;
}
