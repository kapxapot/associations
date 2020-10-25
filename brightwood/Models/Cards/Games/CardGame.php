<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\DrawEvent;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;
use Webmozart\Assert\Assert;

abstract class CardGame implements SerializableInterface
{
    /**
     * Required - set either using the constructor, or using withDeck().
     */
    private ?Deck $deck;

    /**
     * Empty by default.
     */
    private Pile $discard;

    /**
     * Empty by default.
     */
    private Pile $trash;

    /**
     * Required - set either using the constructor, or using withPlayers().
     */
    private ?PlayerCollection $players;

    private ?array $nextPlayers = null;

    private ?Player $starter;
    private bool $isStarted = false;

    private ?Player $observer;

    public function __construct(
        ?Deck $deck = null,
        ?Pile $discard = null,
        ?PlayerCollection $players = null
    )
    {
        $this
            ->withDeck($deck)
            ->withDiscard($discard ?? new Pile())
            ->withTrash(new Pile())
            ->withPlayers($players);
    }

    public function deck() : Deck
    {
        Assert::notNull($this->deck);

        return $this->deck;
    }

    protected function hasDeck() : bool
    {
        return $this->deck !== null;
    }

    /**
     * @return $this
     */
    public function withDeck(?Deck $deck) : self
    {
        $this->deck = $deck;

        return $this;
    }

    public function discard() : Pile
    {
        return $this->discard;
    }

    /**
     * @return $this
     */
    public function withDiscard(Pile $discard) : self
    {
        $this->discard = $discard;

        return $this;
    }

    public function trash() : Pile
    {
        return $this->trash;
    }

    /**
     * @return $this
     */
    public function withTrash(Pile $trash) : self
    {
        $this->trash = $trash;

        return $this;
    }

    public function players() : PlayerCollection
    {
        Assert::notEmpty($this->players);

        Assert::countBetween(
            $this->players,
            static::minPlayers(),
            static::maxPlayers()
        );

        return $this->players;
    }

    /**
     * @return $this
     */
    public function withPlayers(?PlayerCollection $players) : self
    {
        $this->players = $players;

        if ($players) {
            $this->withStarter($players->first());
            $this->withObserver($players->last());
        }

        return $this;
    }

    /**
     * Who goes first?
     */
    protected function starter() : Player
    {
        return $this->starter;
    }

    /**
     * @return $this
     */
    public function withStarter(Player $player) : self
    {
        Assert::true(
            $this->isValidPlayer($player)
        );

        $this->starter = $player;

        return $this;
    }

    public function isStarted() : bool
    {
        return $this->isStarted;
    }

    /**
     * @return $this
     */
    public function withIsStarted(bool $isStarted) : self
    {
        $this->isStarted = $isStarted;

        return $this;
    }

    protected function observer() : Player
    {
        return $this->observer;
    }

    /**
     * @return $this
     */
    public function withObserver(Player $player) : self
    {
        $this->observer = $player;

        return $this;
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
        return $this->players()->any(
            fn (Player $p) => $p->equals($player)
        );
    }

    protected function nextPlayer(Player $player) : Player
    {
        if (is_null($this->nextPlayers)) {
            $this->initNextPlayers();
        }

        return $this->nextPlayers[$player->id()];
    }

    /**
     * Builds a support array for quick next player retrieval.
     */
    private function initNextPlayers() : void
    {
        $this->nextPlayers = [];

        /** @var Player|null */
        $prev = null;

        foreach ($this->players() as $player) {
            if ($prev) {
                $this->nextPlayers[$prev->id()] = $player;
            }

            $prev = $player;
        }

        $this->nextPlayers[$prev->id()] = $this->players()->first();
    }

    /**
     * Returns top card from discard pile. Null in case of no cards.
     */
    protected function topDiscard() : ?Card
    {
        return $this->discard->top();
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
     * Deals cards and marks the game as started.
     */
    public function start() : MessageInterface
    {
        Assert::false($this->isStarted);
        Assert::notNull($this->starter());

        $message = $this->dealing();

        $this->isStarted = true;

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
            foreach ($this->players() as $player) {
                $drawEvent = $this->drawToHand($player, 1);

                if (is_null($drawEvent)) {
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
     * Tries to draw cards from the deck.
     * 
     * If any cards are drawn, they are added to the player's hand
     * and a {@see DrawEvent} is returned.
     * 
     * Otherwise, the null is returned (which means that no cards where drawn).
     * 
     * @throws \InvalidArgumentException
     */
    public function drawToHand(Player $player, int $amount = 1) : ?DrawEvent
    {
        Assert::true($this->isValidPlayer($player));
        Assert::false($this->isDeckEmpty());

        $drawn = $this->deck->drawMany($amount);

        if ($drawn->isEmpty()) {
            return null;
        }

        $player->addCards($drawn);

        return new DrawEvent($player, $drawn);
    }

    public function drawToDiscard(int $amount = 1) : CardCollection
    {
        Assert::false($this->isDeckEmpty());

        $drawn = $this->deck->drawMany($amount);

        if ($drawn->any()) {
            $this->discard->addMany($drawn);

            $drawn->apply(
                fn (Card $c) => $this->onDiscard($c)
            );
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

        $this->onDiscard($card, $player);
    }

    /**
     * Override this method to apply some logic on every card discard.
     */
    protected function onDiscard(Card $card, ?Player $player = null) : void
    {
        // nothing here
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

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return UniformSerializer::serialize(
            $this,
            [
                'players' => $this->players,
                'deck' => $this->deck,
                'discard' => $this->discard,
                'trash' => $this->trash,
                'starter_id' => $this->starter()->id(),
                'is_started' => $this->isStarted,
                'observer_id' => $this->observer()->id(),
            ],
            ...$data
        );
    }
}
