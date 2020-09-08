<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Sets\Decks\FullDeck;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Webmozart\Assert\Assert;

class EightsGame extends CardGame
{
    private Player $starter;
    private bool $started = false;

    private int $moves = 0;
    private int $maxMoves = 100; // temp. safeguard

    public function __construct(
        Player ...$players
    )
    {
        parent::__construct(
            new FullDeck(),
            ...$players
        );

        $this->starter = $this->players->first();
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

    public function isFinished() : bool
    {
        return $this->isStarted() && ($this->hasWinner() || $this->isDraw());
    }

    /**
     * @return MessageInterface[]
     */
    public function run() : array
    {
        $messages = [];

        $messages[] = $this->start();
        $messages[] = new Message(['Игра начинается!']);

        $player = $this->starter;

        while (!$this->isFinished()) {
            $messages[] = $this->makeMove($player);

            if ($this->hasWon($player)) {
                $messages[] = new Message(
                    [$player . ' {выиграл|выиграла}!']
                );

                break;
            }

            $player = $this->nextPlayer($player);
        }

        if ($this->isDraw()) {
            $messages[] = new Message(['Ничья!']);
        }

        return $messages;
    }

    public function start() : MessageInterface
    {
        Assert::false($this->started);
        Assert::notNull($this->starter);

        $message = $this->dealing();

        $this->started = true;

        return $message;
    }

    private function dealing() : MessageInterface
    {
        $count = $this->players->count();

        switch ($count) {
            case 2:
                $amount = 7;
                break;

            case 3:
                $amount = 5;
                break;

            default:
                $amount = 4;
        }

        $lines = [];

        $this->deal($amount);

        $lines[] = 'Раздаем по ' . $amount . ' карт';

        $cards = $this->drawToDiscard();

        $lines[] = (!$cards->isEmpty())
            ? 'Кладем ' . $cards . ' из колоды на стол'
            : 'Че... Где все карты?';

        return new Message($lines);
    }

    public function makeMove(Player $player) : MessageInterface
    {
        Assert::true($this->isValidPlayer($player));
        Assert::true($this->started);

        $lines = [];

        if ($this->hasWon($player)) {
            $lines[] = $player . ' уже {выиграл|выиграла}!';
        }

        $this->moves++;

        $lines[] = 'Ходит <b>' . $player . '</b> (' . $this->moves . ')';

        $lines = array_merge(
            $lines,
            $this->actualMove($player),
            $this
                ->players
                ->map(
                    fn (Player $p) =>
                    $p->name() . ' (' . $p->hand()->size() . '): ' . $p->hand()
                )
                ->toArray()
        );

        $lines[] = 'Стол: ' . $this->topDiscard();
        $lines[] = 'Колода: ' . $this->deckSize();

        return new Message($lines);
    }

    /**
     * Returns top card from discard pile. Null in case of no cards.
     */
    private function topDiscard() : ?Card
    {
        return $this->discard->top();
    }

    /**
     * @return string[]
     */
    private function actualMove(Player $player) : array
    {
        $lines = [];

        while (true) {
            $putCard = $this->tryPutCard($player);

            if ($putCard) {
                $lines[] = $player . ' кладет ' . $putCard . ' на стол';
                break;
            }

            if ($this->isDeckEmpty()) {
                break;
            }

            $drawn = $this->drawToHand($player);

            if ($drawn->any()) {
                $lines[] = $player . ' берет ' . $drawn . ' из колоды';
            }
        }

        return $lines;
    }

    private function tryPutCard(Player $player) : ?Card
    {
        $topDiscard = $this->topDiscard();

        Assert::notNull($topDiscard);

        /** @var Card|null */
        $suitableCard = $player
            ->hand()
            ->cards()
            ->where(
                fn (Card $c) => $this->compatible($c, $topDiscard)
            )
            ->random();

        if ($suitableCard) {
            $this->discardFromHand($player, $suitableCard);
        }

        return $suitableCard;
    }

    public function compatible(Card $one, Card $two) : bool
    {
        if (Joker::is($one) || Joker::is($two)) {
            return true;
        }

        if (!SuitedCard::is($one) || !SuitedCard::is($two)) {
            return false;
        }

        /** @var SuitedCard $one */
        /** @var SuitedCard $two */

        return $one->isSameSuit($two) || $one->isSameRank($two);
    }

    public function winner() : ?Player
    {
        if (!$this->started) {
            return null;
        }

        return $this->players->first(
            fn (Player $p) => $this->hasWon($p)
        );
    }

    private function hasWinner() : bool
    {
        return $this->winner() !== null;
    }

    private function hasWon(Player $player) : bool
    {
        return $player->hand()->isEmpty();
    }

    private function isDraw() : bool
    {
        return $this->moves >= $this->maxMoves;
    }
}
