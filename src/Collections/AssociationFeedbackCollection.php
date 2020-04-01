<?php

namespace App\Collections;

use App\Models\AssociationFeedback;
use App\Models\User;

class AssociationFeedbackCollection extends FeedbackCollection
{
    protected string $class = AssociationFeedback::class;

    public function firstBy(User $user) : ?AssociationFeedback
    {
        return $this->first(
            fn (AssociationFeedback $f) => $f->isCreatedBy($user)
        );
    }
}
