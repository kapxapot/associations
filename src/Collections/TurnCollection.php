<?php

namespace App\Collections;

use App\Models\Turn;
use App\Models\User;
use Plasticode\TypedCollection;
use Plasticode\Util\Sort;

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
                ->clean()
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

    /**
     * Groups turns by their user ids.
     *
     * @return array<int, TurnCollection>
     */
    public function groupByUser() : array
    {
        return $this
            ->where(
                fn (Turn $t) => !is_null($t->user())
            )
            ->asc(
                fn (Turn $t) => $t->createdAt,
                Sort::DATE
            )
            ->group(
                fn (Turn $t) => $t->user()->getId()
            );
    }
}
