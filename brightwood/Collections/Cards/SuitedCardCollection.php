<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\SuitedCard;

class SuitedCardCollection extends CardCollection
{
    protected string $class = SuitedCard::class;

    /**
     * Returns distinct suits from the collection.
     */
    public function suits() : SuitCollection
    {
        return
            SuitCollection::from(
                $this->map(
                    fn (SuitedCard $c) => $c->suit()
                )
            )
            ->distinct();
    }
}
