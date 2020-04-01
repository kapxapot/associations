<?php

namespace App\Collections;

use App\Models\Association;
use App\Models\User;

class AssociationCollection extends LanguageElementCollection
{
    protected string $class = Association::class;

    public function playableAgainst(User $user) : self
    {
        return $this->where(
            fn (Association $a) => $a->isPlayableAgainst($user)
        );
    }
}
