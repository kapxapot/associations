<?php

namespace App\Collections;

use App\Models\User;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\TypedCollection;

class UserCollection extends TypedCollection
{
    protected string $class = User::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }
}
