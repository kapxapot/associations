<?php

namespace App\Collections;

use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\Collections\Generic\DbModelCollection;

class LanguageElementCollection extends DbModelCollection
{
    protected string $class = LanguageElement::class;

    /**
     * Filters fuzzy public elements.
     *
     * @return static
     */
    public function public(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isFuzzyPublic()
        );
    }

    /**
     * Filters fuzzy private elements.
     *
     * @return static
     */
    public function private(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isFuzzyPrivate()
        );
    }

    /**
     * @return static
     */
    public function disabled(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isDisabled()
        );
    }

    /**
     * @return static
     */
    public function mature(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isMature()
        );
    }

    /**
     * @return static
     */
    public function notMature(): self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isMature()
        );
    }

    /**
     * Returns elements visible to everyone (equivalent to "not mature").
     * 
     * @return static
     */
    public function visible(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isVisible()
        );
    }

    /**
     * @return static
     */
    public function visibleFor(?User $user): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isVisibleFor($user)
        );
    }

    /**
     * @return static
     */
    public function invisibleFor(?User $user): self
    {
        return $this->where(
            fn (LanguageElement $el) => !$el->isVisibleFor($user)
        );
    }
}
