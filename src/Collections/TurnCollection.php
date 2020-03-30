<?php

namespace App\Collections;

use App\Models\Turn;
use App\Models\User;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\TypedCollection;

class TurnCollection extends TypedCollection
{
    protected string $class = Turn::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }

    public function anyBy(User $user) : bool
    {
        return $this->any(
            fn (Turn $t) => $t->isBy($user)
        );
    }

    public function users() : UserCollection
    {
        return UserCollection::from(
            $this
                ->map(
                    fn (Turn $t) => $t->user()
                )
                ->distinct()
        );
    }
}
