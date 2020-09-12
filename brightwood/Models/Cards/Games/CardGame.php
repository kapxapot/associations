<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Sets\Decks\Deck;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Webmozart\Assert\Assert;

abstract class CardGame
{
    protected Deck $deck;
    protected Pile $discard;
    protected Pile $trash;

    protected PlayerCollection $players;
    protected array $nextPlayers;

    protected Player $starter;
    protected bool $started = false;

    public function __construct(
        Deck $deck,
        Player ...$players
    )
    {
        Assert::notEmpty($players);

        $this->deck = $deck;
        $this->discard = new Pile();
        $this->trash = new Pile();

        $this->players = PlayerCollection::make($players);

        Assert::true($this->isValidPlayerCount());

        $this->starter = $this->players->first();

        $this->initNextPlayers();
    }

    private function isValidPlayerCount() : bool
    {
        $count = $this->players->count();

        return $count >= static::minPlayers()
            && $count <= static::maxPlayers();
    }

    /**
     * Override this if needed.
     */
    public static function minPlayers() : int
    {
        return 2;
    }

    /**
     * Provide this number in the *real* game.
     */
    abstract public static function maxPlayers() : int;

    protected function isValidPlayer(Player $player) : bool
    {
        return $this->players->any(
            fn (Player $p) => $p->equals($player)
        );
    }

    /**
     * Build a support array for quick next player retrieval.
     */
    private function initNextPlayers()
    {
        $this->nextPlayers = [];

        /** @var Player|null */
        $prev = null;

        foreach ($this->players as $player) {
            if ($prev) {
                $this->nextPlayers[$prev->id()] = $player;
            }

            $prev = $player;
        }

        $this->nextPlayers[$prev->id()] = $this->players->first();
    }

    protected function nextPlayer(Player $player) : Player
    {
        return $this->nextPlayers[$player->id()];
    }

    /**
     * Who goes first?
     */
    public function starter() : Player
    {
        return $this->starter;
    }

    /**
     * @return static
     */
    public function withStarter(Player $player) : self
    {
        Assert::true($this->isValidPlayer($player));

        $this->starter = $player;

        return $this;
    }

    public function isStarted() : bool
    {
        return $this->started;
    }

    public function deck() : Deck
    {
        return $this->deck;
    }

    public function discard() : Pile
    {
        return $this->discard;
    }

    /**
     * Returns top card from discard pile. Null in case of no cards.
     */
    protected function topDiscard() : ?Card
    {
        return $this->discard->top();
    }

    public function trash() : Pile
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

    public function start() : MessageInterface
    {
        Assert::false($this->started);
        Assert::notNull($this->starter);

        $message = $this->dealing();

        $this->started = true;

        return $message;
    }

    abstract protected function dealing() : MessageInterface;

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
        Assert::true($this->isValidPlayer($player));
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
        Assert::true($this->isValidPlayer($player));
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
        Assert::true($this->isValidPlayer($player));
        Assert::true($player->hasCard($card));

        $player->removeCard($card);
        $this->discard->add($card);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function trashFromHand(Player $player, Card $card) : void
    {
        Assert::true($this->isValidPlayer($player));
        Assert::true($player->hasCard($card));

        $player->removeCard($card);
        $this->trash->add($card);
    }
}
