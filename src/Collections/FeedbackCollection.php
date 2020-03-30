<?php

namespace App\Collections;

use App\Models\Feedback;
use App\Models\User;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\TypedCollection;

class FeedbackCollection extends TypedCollection
{
    protected string $class = Feedback::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }

    public function dislikes() : self
    {
        return self::from(
            $this->where(
                fn (Feedback $f) => $f->isDisliked()
            )
        );
    }

    public function matures() : self
    {
        return self::from(
            $this->where(
                fn (Feedback $f) => $f->isMature()
            )
        );
    }

    public function firstBy(User $user) : ?Feedback
    {
        return $this->first(
            fn (Feedback $f) => $f->isCreatedBy($user)
        );
    }

    public function anyBy(User $user) : bool
    {
        return $this->any(
            fn (Feedback $f) => $f->isCreatedBy($user)
        );
    }
}
