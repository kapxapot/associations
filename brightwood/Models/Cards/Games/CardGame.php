<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Sets\CardList;
use Brightwood\Models\Cards\Sets\Decks\Deck;
use Brightwood\Models\Cards\Sets\Pile;
use Webmozart\Assert\Assert;

class CardGame
{
    protected Deck $deck;
    protected Pile $discard;
    protected Pile $trash;

    protected PlayerCollection $players;

    public function __construct(
        Deck $deck,
        PlayerCollection $players
    )
    {
        Assert::notEmpty($players);

        $this->deck = $deck;
        $this->discard = new Pile();
        $this->trash = new Pile();

        $this->players = $players;
    }

    public function deck() : CardList
    {
        return $this->deck;
    }

    public function discard() : CardList
    {
        return $this->discard;
    }

    public function trash() : CardList
    {
        return $this->trash;
    }

    public function players() : PlayerCollection
    {
        return $this->players;
    }

    public function deckSize() : int
    {
        return $this->deck->size();
    }

    public function isDeckEmpty() : bool
    {
        return $this->deckSize() == 0;
    }

    public function discardSize() : int
    {
        return $this->discard->size();
    }

    public function isDiscardEmpty() : bool
    {
        return $this->discardSize() == 0;
    }

    /**
     * Tries to deal $amount cards to every player.
     * If there is not enough cards in deck or the amount = 0, deals all cards.
     * 
     * @throws \InvalidArgumentException
     */
    public function deal(int $amount = 0) : void
    {
        Assert::false($this->isDeckEmpty());
        Assert::greaterThanEq($amount, 0);

        $dealed = 0;

        while (!$amount || ($dealed < $amount)) {
            foreach ($this->players as $player) {
                $drawn = $this->drawToHand($player, 1);

                if ($drawn->isEmpty()) {
                    break;
                }
            }

            if ($this->isDeckEmpty()) {
                break;
            }

            $dealed++;
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function drawToHand(Player $player, int $amount = 1) : CardCollection
    {
        Assert::false($this->isDeckEmpty());

        $drawn = $this->deck->drawMany($amount);

        if ($drawn->any()) {
            $player->addCards($drawn);
        }

        return $drawn;
    }

    public function drawToDiscard(int $amount = 1) : CardCollection
    {
        Assert::false($this->isDeckEmpty());

        $drawn = $this->deck->drawMany($amount);

        if ($drawn->any()) {
            $this->discard->addMany($drawn);
        }

        return $drawn;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function takeFromDiscard(Player $player, int $amount = 1) : CardCollection
    {
        Assert::false($this->isDiscardEmpty());

        $taken = $this->discard->takeMany($amount);

        if ($taken->any()) {
            $player->addCards($taken);
        }

        return $taken;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function discardFromHand(Player $player, Card $card) : void
    {
        Assert::true($player->hasCard($card));

        $player->removeCard($card);
        $this->discard->add($card);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function trashFromHand(Player $player, Card $card) : void
    {
        Assert::true($player->hasCard($card));

        $player->removeCard($card);
        $this->trash->add($card);
    }
}
