<?php

namespace App\Collections;

use App\Models\Turn;
use App\Models\User;
use Plasticode\TypedCollection;

class TurnCollection extends TypedCollection
{
    protected string $class = Turn::class;

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

    public function words() : WordCollection
    {
        return WordCollection::from(
            $this
                ->map(
                    fn (Turn $t) => $t->word()
                )
                ->distinct()
        );
    }
}
