<?php

namespace App\Collections;

use App\Models\User;
use Plasticode\TypedCollection;

class UserCollection extends TypedCollection
{
    protected string $class = User::class;
}
