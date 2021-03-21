<?php

namespace App\Collections;

use App\Models\Feedback;
use App\Models\User;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Util\Sort;

class FeedbackCollection extends DbModelCollection
{
    protected string $class = Feedback::class;

    public function dislikes(): self
    {
        return $this->where(
            fn (Feedback $f) => $f->isDisliked()
        );
    }

    public function matures(): self
    {
        return $this->where(
            fn (Feedback $f) => $f->isMature()
        );
    }

    public function firstBy(User $user): ?Feedback
    {
        return $this->first(
            fn (Feedback $f) => $f->isCreatedBy($user)
        );
    }

    public function anyBy(User $user): bool
    {
        return $this->any(
            fn (Feedback $f) => $f->isCreatedBy($user)
        );
    }

    /**
     * Sorts the collection by updatedAt DESC.
     *
     * @return static
     */
    public function sort(): self
    {
        return $this->desc(
            fn (UpdatedAtInterface $u) => $u->updatedAt,
            Sort::DATE
        );
    }
}
