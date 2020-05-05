<?php

namespace App\Collections;

use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\Collections\Basic\DbModelCollection;

class LanguageElementCollection extends DbModelCollection
{
    protected string $class = LanguageElement::class;

    /**
     * @return static
     */
    public function approved() : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isApproved()
        );
    }

    /**
     * @return static
     */
    public function notApproved() : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isApproved()
        );
    }

    /**
     * @return static
     */
    public function mature() : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isMature()
        );
    }

    /**
     * @return static
     */
    public function notMature() : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isMature()
        );
    }

    /**
     * @return static
     */
    public function visibleFor(?User $user) : self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isVisibleFor($user)
        );
    }

    /**
     * @return static
     */
    public function invisibleFor(?User $user) : self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isVisibleFor($user)
        );
    }
}
