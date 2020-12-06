<?php

namespace App\Collections;

use App\Models\Association;
use App\Models\User;
use Plasticode\Util\Sort;

class AssociationCollection extends LanguageElementCollection
{
    protected string $class = Association::class;

    public function playableAgainst(User $user) : self
    {
        return $this->where(
            fn (Association $a) => $a->isPlayableAgainst($user)
        );
    }

    public function random() : ?Association
    {
        return parent::random();
    }

    public function oldest() : ?Association
    {
        return $this
            ->asc(
                fn (Association $a) => $a->createdAt,
                Sort::DATE
            )
            ->first();
    }
}
