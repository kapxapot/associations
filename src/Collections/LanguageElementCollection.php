<?php

namespace App\Collections;

use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\TypedCollection;

class LanguageElementCollection extends TypedCollection
{
    protected string $class = LanguageElement::class;

    public function approved() : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isApproved()
        );
    }

    public function notApproved() : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isApproved()
        );
    }

    public function mature() : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isMature()
        );
    }

    public function notMature() : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isMature()
        );
    }

    public function visibleFor(User $user) : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isVisibleFor($user)
        );
    }

    public function invisibleFor(User $user) : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isVisibleFor($user)
        );
    }
}
