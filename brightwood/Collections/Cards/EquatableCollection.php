<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Plasticode\Collections\Generic\TypedCollection;

class EquatableCollection extends TypedCollection
{
    protected string $class = EquatableInterface::class;

    /**
     * Returns distinct element using contains() function.
     * 
     * @return static
     */
    public function distinct(): self
    {
        $col = static::make();

        /** @var EquatableInterface */
        foreach ($this as $item) {
            if ($col->contains($item)) {
                continue;
            }

            $col = $col->add($item);
        }

        return $col;
    }

    /**
     * Returns all elements except the specified.
     *
     * @return static
     */
    public function except(EquatableInterface ...$elements): self
    {
        $toExclude = static::make($elements);

        return $this->where(
            fn (EquatableInterface $eq) => !$toExclude->contains($eq)
        );
    }

    public function contains(?EquatableInterface $element): bool
    {
        if (is_null($element)) {
            return false;
        }

        return $this->anyFirst(
            fn (EquatableInterface $eq) => $eq->equals($element)
        );
    }
}
