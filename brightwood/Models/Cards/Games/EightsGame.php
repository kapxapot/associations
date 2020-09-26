<?php

namespace Brightwood\Models\Cards\Games;

use App\Semantics\Sentence;
use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\MessageCollection;
use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Actions\Eights\JackGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SevenGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\CardEventAccumulator;
use Brightwood\Models\Cards\Events\DiscardEvent;
use Brightwood\Models\Cards\Events\DrawEvent;
use Brightwood\Models\Cards\Events\NoCardsEvent;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Decks\FullDeck;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Parsing\StoryParser;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

class EightsGame extends CardGame
{
    private int $moves = 0;
    private int $maxMoves = 1000; // safeguard

    private StoryParser $parser;
    private Cases $cases;

    private bool $showPlayersLine = false;

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
            new EightsDiscard(),
            ...$players
        );

        $this->parser = $parser;
        $this->cases = $cases;
    }

    public function withPlayersLine() : self
    {
        $this->showPlayersLine = true;

        return $this;
    }

    public function discard() : EightsDiscard
    {
        return $this->discard;
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

    public function run() : MessageCollection
    {
        $messages = [];

        $messages[] = $this
            ->start()
            ->appendLines(
                //'Наблюдает за игрой: ' . $this->observer,
                'Игра начинается!'
            );

        $player = $this->starter;

        while (!$this->isFinished()) {
            $messages[] = $this->makeMove($player);

            if ($this->hasWon($player)) {
                $messages[] = new TextMessage(
                    $player->equals($this->observer)
                        ? $player->personalName() . ' выиграли!'
                        : $this->parseFor($player, $player . ' {выиграл|выиграла}!')
                );

                break;
            }

            $player = $this->nextPlayer($player);
        }

        if ($this->isDraw()) {
            $messages[] = new TextMessage(
                $this->drawReason(),
                'Ничья!'
            );
        }

        return MessageCollection::make($messages);
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

        $this->deal($amount);

        $message = new TextMessage(
            'Раздаем по ' . $amount . ' ' .
            $this->cases->caseForNumber('карта', $amount)
        );

        $cards = $this->drawToDiscard();

        return $message->appendLines(
            !$cards->isEmpty()
                ? 'Кладем ' . $cards . ' из колоды на стол'
                : 'Че... Где все карты?'
        );
    }

    public function makeMove(Player $player) : MessageInterface
    {
        Assert::true($this->isValidPlayer($player));
        Assert::true($this->started);

        $this->moves++;

        $moveStatus =
            '[' . $this->moves . '] ' .
            'Стол: ' . $this->discard()->topString() . ', ' .
            'Колода: ' . $this->deckSize();

        $moveResults = $this
            ->actualMove($player)
            ->messagesFor($this->observer);

        $message = (new TextMessage($moveStatus))
            ->appendLines(...$moveResults);

        if ($this->showPlayersLine) {
            $playersLine = $this->players->handsString();
            $message = $message->appendLines($playersLine);
        }

        return $message;
    }

    private function actualMove(Player $player) : CardEventAccumulator
    {
        $events = new CardEventAccumulator();

        $gift = $this->gift;
        $this->gift = null;

        if ($gift instanceof ApplicableActionInterface) {
            $giftEvents = $gift->applyTo($this, $player);
            $events->addMany($giftEvents);
        }

        if ($events->hasSkip()) {
            return $events;
        }

        // drawing & trying to put a card
        while (true) {
            $putCard = $this->tryPutCard($player);

            if ($putCard) {
                $events->add(
                    new DiscardEvent(
                        $player,
                        CardCollection::collect($putCard)
                    )
                );

                // if we already have a winner, no need to make gifts
                if (!$this->hasWinner()) {
                    $gift = $this->toGift($player, $putCard);

                    if ($gift) {
                        $this->gift = $gift;

                        $events->addMany(
                            $gift->initialEvents()
                        );
                    }
                }

                $this->noCardsInARow = 0;

                break;
            }

            if ($this->isDeckEmpty()) {
                $events->add(
                    new NoCardsEvent($player)
                );

                $this->noCardsInARow++;

                break;
            }

            $drawn = $this->drawToHand($player);

            if ($drawn->any()) {
                $events->add(
                    new DrawEvent($player, $drawn)
                );
            }
        }

        return $events;
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
            return new JackGiftAction($player, $card);
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

        $topDiscard = $this->discard()->actualTop();

        if (is_null($topDiscard) || $topDiscard->isJoker()) {
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
