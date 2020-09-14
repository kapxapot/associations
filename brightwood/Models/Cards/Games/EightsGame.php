<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Interfaces\RestrictingInterface;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Moves\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Moves\Actions\Eights\SevenGiftAction;
use Brightwood\Models\Cards\Moves\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Moves\Actions\GiftAction;
use Brightwood\Models\Cards\Moves\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Moves\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Moves\Actions\SkipGiftAction;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Decks\FullDeck;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Parsing\StoryParser;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

class EightsGame extends CardGame
{
    private int $moves = 0;
    private int $maxMoves = 1000; // safeguard

    private StoryParser $parser;
    private Cases $cases;

    /**
     * Gift from the previous player.
     */
    private ?GiftAction $gift = null;

    /**
     * Accumulates the count of players in a row who have no cards to put.
     */
    private int $noCardsInARow = 0;

    public function __construct(
        StoryParser $parser,
        Cases $cases,
        Player ...$players
    )
    {
        parent::__construct(
            new FullDeck(),
            ...$players
        );

        $this->parser = $parser;
        $this->cases = $cases;
    }

    public static function maxPlayers(): int
    {
        return 10;
    }

    public function parseFor(Player $player, string $text) : string
    {
        return $this->parser->parse($player, $text);
    }

    public function isFinished() : bool
    {
        return $this->isStarted() && ($this->hasWinner() || $this->isDraw());
    }

    /**
     * If some jokers are on the top, the actual top is underneath them.
     */
    private function actualTopDiscard() : ?Card
    {
        $cards = $this->discard->cards();

        $actual = $cards->last(
            fn (Card $c) => !($c instanceof Joker)
        );

        return $actual ?? $cards->last();
    }

    private function topDiscardStr() : ?string
    {
        $top = $this->topDiscard();

        if (is_null($top)) {
            return null;
        }

        $actual = $this->actualTopDiscard();

        if ($top->equals($actual) && !$actual->hasRestriction()) {
            return $top->toString();
        }

        return
            $top . ' ('
            . ($actual->hasRestriction()
                ? $actual->restriction()->restrictionStr()
                : $actual)
            . ')';
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
                    [
                        $this->parseFor($player, $player . ' {выиграл|выиграла}!')
                    ]
                );

                break;
            }

            $player = $this->nextPlayer($player);
        }

        if ($this->isDraw()) {
            $messages[] = new Message(
                [
                    $this->drawReason(),
                    'Ничья!'
                ]
            );
        }

        return $messages;
    }

    protected function dealing() : MessageInterface
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

        $lines[] =
            'Раздаем по ' . $amount . ' ' .
            $this->cases->caseForNumber('карта', $amount);

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
            $lines[] = $this->parseFor($player, $player . ' уже {выиграл|выиграла}!');
        }

        $this->moves++;

        $lines[] =
            '[' . $this->moves . '] ' .
            'Стол: ' . $this->topDiscardStr() . ', Колода: ' . $this->deckSize();;

        $lines[] = '';

        $lines = array_merge(
            $lines,
            $this->actualMove($player)
        );

        $lines[] = '';

        $lines[] = implode(
            ', ',
            $this
                ->players
                ->map(
                    fn (Player $p) =>
                    $p->name() . ' (' . $p->handSize() . ')'
                )
                ->toArray()
        );

        return new Message($lines);
    }

    /**
     * @return string[]
     */
    private function actualMove(Player $player) : array
    {
        $lines = [];

        $gift = $this->gift;
        $this->gift = null;

        if ($gift) {
            if ($gift instanceof ApplicableActionInterface) {
                $lines = array_merge(
                    $lines,
                    $gift->applyTo($this, $player)
                );
            }

            if ($gift instanceof SkipActionInterface) {
                $lines[] = $player . ' пропускает ход';

                return $lines;
            }
        }

        // drawing & trying to put a card
        while (true) {
            $putCard = $this->tryPutCard($player);

            if ($putCard) {
                $lines[] = $player . ' кладет ' . $putCard . ' на стол';

                // if we already have a winner, no need to make gifts
                if (!$this->hasWinner()) {
                    $gift = $this->toGift($player, $putCard);

                    if ($gift) {
                        $this->gift = $gift;

                        $lines = array_merge(
                            $lines,
                            $gift->getMessages()
                        );
                    }
                }

                $this->noCardsInARow = 0;

                break;
            }

            if ($this->isDeckEmpty()) {
                $lines[] = $player . ' пропускает ход (нет карт)';

                $this->noCardsInARow++;

                break;
            }

            $drawn = $this->drawToHand($player);

            if ($drawn->any()) {
                $lines[] = $player . ' берет ' . $drawn . ' из колоды';
            }
        }

        return $lines;
    }

    private function toGift(Player $player, Card $card) : ?GiftAction
    {
        if (!($card instanceof SuitedCard)) {
            return null;
        }

        // 6
        
        if ($card->isRank(Rank::six())) {
            return new SixGiftAction($player, $card);
        }

        // 7

        if ($card->isRank(Rank::seven())) {
            return new SevenGiftAction($player, $card);
        }

        // jack

        if ($card->isRank(Rank::jack())) {
            return new SkipGiftAction($player, $card);
        }

        // 8

        if ($card->isRank(Rank::eight())) {
            $suit = $this->chooseSuit($player);
            $action = new EightGiftAction($player, $card, $suit);

            $card->addRestriction($action);

            return $action;
        }

        return null;
    }

    private function chooseSuit(Player $player) : Suit
    {
        // todo: extract this to strategy

        $suited = $player->hand()->suitedCards();

        return $suited->any()
            ? $suited->suits()->random()
            : Suit::random();
    }

    private function tryPutCard(Player $player) : ?Card
    {
        // todo: extract this to strategy

        /** @var Card|null */
        $suitableCard = $player
            ->hand()
            ->cards()
            ->where(
                fn (Card $c) => $this->canBeDiscarded($c)
            )
            ->random();

        if ($suitableCard) {
            $this->discardFromHand($player, $suitableCard);
        }

        return $suitableCard;
    }

    public function canBeDiscarded(Card $card) : bool
    {
        if ($this->isSuperCard($card)) {
            return true;
        }

        $topDiscard = $this->actualTopDiscard();

        if (
            is_null($topDiscard)
            || $topDiscard->isJoker()
        ) {
            return true;
        }

        // for 8 suit
        if ($topDiscard->hasRestriction()) {
            return $topDiscard->restriction()->isCompatible($card);
        }

        // currently, at this point both cards can only be suited here
        if (!($topDiscard instanceof SuitedCard) || !($card instanceof SuitedCard)) {
            return false;
        }

        return $topDiscard->isSameSuit($card) || $topDiscard->isSameRank($card);
    }

    private function isSuperCard(Card $card) : bool
    {
        return $card->isJoker() || $card->isRank(Rank::eight());
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
        return $this->drawReason() !== null;
    }

    private function drawReason() : ?string
    {
        if ($this->moves >= $this->maxMoves) {
            return 'Превышено максимальное число ходов (' . $this->maxMoves . '), что-то явно не так!';
        }

        if ($this->noCardsInARow >= $this->players->count()) {
            return 'Ни у кого из игроков нет карт для хода';
        }

        return null;
    }
}
