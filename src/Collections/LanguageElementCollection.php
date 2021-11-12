<?php

namespace App\Collections;

use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\Collections\Generic\Collection;
use Plasticode\Collections\Generic\DbModelCollection;

class LanguageElementCollection extends DbModelCollection
{
    protected string $class = LanguageElement::class;

    /**
     * Segregates associations by scope groups.
     *
     * @return Collection Collection of {@see LanguageElementCollection}s.
     */
    public function segregateByScope(): Collection
    {
        return Collection::collect(
            $this->fuzzyPublic(),
            $this->private(),
            $this->fuzzyDisabled(),
        );
    }

    /**
     * Filters fuzzy public elements.
     *
     * @return static
     */
    public function fuzzyPublic(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isFuzzyPublic()
        );
    }

    /**
     * @return static
     */
    public function private(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isPrivate()
        );
    }

    /**
     * @return static
     */
    public function fuzzyDisabled(): self
    {
        return $this->where(
            fn (LanguageElement $el) => $el->isFuzzyDisabled()
        );
    }

    /**
     * Filters the elements by scopes.
     *
     * @return static
     */
    public function scopedTo(int ...$scopes): self
    {
        return $this->where(
            fn (LanguageElement $el) => in_array(intval($el->scope), $scopes)
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
