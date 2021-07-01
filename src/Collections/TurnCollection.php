<?php

namespace App\Collections;

use App\Models\Turn;
use App\Models\User;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Util\Sort;

class TurnCollection extends DbModelCollection
{
    protected string $class = Turn::class;

    public function anyBy(User $user): bool
    {
        return $this->any(
            fn (Turn $t) => $t->isBy($user)
        );
    }

    /**
     * Returns only real users (no AI).
     */
    public function users(): UserCollection
    {
        return UserCollection::fromDistinct(
            $this->cleanMap(
                fn (Turn $t) => $t->user()
            )
        );
    }

    public function hasAiTurn(): bool
    {
        return $this->any(
            fn (Turn $t) => $t->isAiTurn()
        );
    }

    /**
     * Returns _distinct_ words from turns.
     */
    public function words(): WordCollection
    {
        return WordCollection::fromDistinct(
            $this->map(
                fn (Turn $t) => $t->word()
            )
        );
    }

    /**
     * Groups turns by their user ids.
     *
     * @return array<int, TurnCollection>
     */
    public function groupByUser(): array
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
