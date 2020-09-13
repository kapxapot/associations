<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Plasticode\Collections\Basic\TypedCollection;

class EquatableCollection extends TypedCollection
{
    protected string $class = EquatableInterface::class;

    /**
     * Returns distinct element using contains() function.
     * 
     * @return static
     */
    public function distinct() : self
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

    public function contains(?EquatableInterface $element) : bool
    {
        if (!$element) {
            return false;
        }

        return $this->anyFirst(
            fn (EquatableInterface $eq) => $eq->equals($element)
        );
    }
}
