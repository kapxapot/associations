<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\CardList;
use Brightwood\Models\Cards\Sets\ExtendableCardList;
use Brightwood\Models\Cards\Sets\Pile;
use Webmozart\Assert\Assert;

/**
 * A specific card list that allows shuffling and dealing.
 * 
 * Shuffled by default.
 */
abstract class Deck extends CardList
{
    public function __construct(bool $shuffle = true)
    {
        parent::__construct(
            $this->build()
        );

        if ($shuffle) {
            $this->shuffle();
        }
    }

    abstract protected function build() : CardCollection;

    /**
     * @return static
     */
    public function shuffle() : self
    {
        $this->cards = $this->cards->shuffle();

        return $this;
    }

    /**
     * Tries to deal $amount cards to every hand.
     * If there is not enough cards in deck or the amount isn't specified, deals all cards.
     * 
     * @param ExtendableCardList[] $hands
     * @return static
     */
    public function deal(array $hands, ?int $amount = null) : self
    {
        Assert::notEmpty($hands);

        $dealed = 0;
        $amount ??= $this->size(); // in case of null deal all deck

        while ($dealed < $amount) {
            foreach ($hands as $hand) {
                $card = $this->draw();

                if (!$card) {
                    break;
                }

                $hand->add($card);
            }

            if ($this->isEmpty()) {
                break;
            }

            $dealed++;
        }

        return $this;
    }

    /**
     * Removes first card from deck and returns it.
     * Returns null in case of empty deck.
     */
    private function draw() : ?Card
    {
        $card = $this->cards->first();
        $this->cards = $this->cards->skip(1);

        return $card;
    }

    public function toPile() : Pile
    {
        return new Pile($this->cards);
    }
}
