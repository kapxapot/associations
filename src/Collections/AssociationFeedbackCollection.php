<?php

namespace App\Collections;

use App\Models\AssociationFeedback;
use App\Models\User;
use Plasticode\Interfaces\ArrayableInterface;

class AssociationFeedbackCollection extends FeedbackCollection
{
    protected string $class = AssociationFeedback::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }

    public function dislikes() : self
    {
        return self::from(
            parent::dislikes()
        );
    }

    public function matures() : self
    {
        return self::from(
            parent::matures()
        );
    }

    public function firstBy(User $user) : ?AssociationFeedback
    {
        return $this->first(
            fn (AssociationFeedback $f) => $f->isCreatedBy($user)
        );
    }
}
