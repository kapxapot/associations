<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Models\Cards\Card;
use Webmozart\Assert\Assert;

/**
 * A stack of cards that allows to put and take cards.
 */
final class Pile extends ExtendableCardList
{
    public function take() : ?Card
    {
        return $this->takeMany(1)->cards()->first();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function takeMany(int $count) : CardList
    {
        Assert::greaterThan($count, 0);

        $taken = $this->cards->take(-$count);
        $this->cards = $this->cards->skip(-$count);

        return new CardList($taken);
    }

    public function put(Card $card) : self
    {
        return $this->add($card);
    }

    public function putMany(CardList $list) : self
    {
        return $this->merge($list);
    }

    /**
     * @return static
     */
    public function flip() : self
    {
        $this->cards = $this->cards->reverse();

        return $this;
    }
}
