<?php

namespace App\Collections;

use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\TypedCollection;

class LanguageElementCollection extends TypedCollection
{
    protected string $class = LanguageElement::class;

    public static function from(ArrayableInterface $arrayable): self
    {
        return new static($arrayable->toArray());
    }

    public function approved(): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => $el->isApproved()
            )
        );
    }

    public function notApproved(): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => !$el->isApproved()
            )
        );
    }

    public function mature(): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => $el->isMature()
            )
        );
    }

    public function notMature(): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => !$el->isMature()
            )
        );
    }

    public function visibleFor(User $user): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => $el->isVisibleFor($user)
            )
        );
    }

    public function invisibleFor(User $user): self
    {
        return self::from(
            $this->where(
                fn (LanguageElement $el) => !$el->isVisibleFor($user)
            )
        );
    }
}
