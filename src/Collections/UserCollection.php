<?php

namespace App\Collections;

use App\Models\User;
use Plasticode\Collections\Basic\DbModelCollection;

class UserCollection extends DbModelCollection
{
    protected string $class = User::class;
}
