<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Collections\MessageCollection;
use Brightwood\Factories\Cards\FullDeckFactory;
use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Actions\Eights\JackGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SevenGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\CardEventAccumulator;
use Brightwood\Models\Cards\Events\DiscardEvent;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Events\NoCardsEvent;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Parsing\StoryParser;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

class EightsGame extends CardGame
{
    private int $move = 0;
    private int $maxMoves = 1000; // safeguard

    /**
     * Required - set either using the constructor, or using withParser().
     */
    private ?StoryParser $parser;

    /**
     * Required - set either using the constructor, or using withCases().
     */
    private ?Cases $cases;

    private bool $showPlayersLine = false;

    /**
     * Gift from the previous player.
     */
    private ?GiftAction $gift = null;

    private ?Player $currentPlayer = null;

    /**
     * Accumulates the count of players in a row who have no cards to put.
     */
    private int $noCardsInARow = 0;

    public function __construct(
        ?StoryParser $parser = null,
        ?Cases $cases = null,
        ?PlayerCollection $players = null,
        ?Deck $deck = null
    )
    {
        parent::__construct($deck, new EightsDiscard(), $players);

        $this->withParser($parser);
        $this->withCases($cases);
    }

    protected function parser() : StoryParser
    {
        Assert::notNull($this->parser);

        return $this->parser;
    }

    /**
     * @return $this
     */
    public function withParser(?StoryParser $parser) : self
    {
        $this->parser = $parser;

        return $this;
    }

    protected function cases() : Cases
    {
        Assert::notNull($this->cases);

        return $this->cases;
    }

    /**
     * @return $this
     */
    public function withCases(?Cases $cases) : self
    {
        $this->cases = $cases;

        return $this;
    }

    public function withPlayersLine() : self
    {
        $this->showPlayersLine = true;

        return $this;
    }

    public function discard() : EightsDiscard
    {
        return parent::discard();
    }

    public function gift() : ?GiftAction
    {
        return $this->gift;
    }

    /**
     * @return $this
     */
    public function withGift(?GiftAction $gift) : self
    {
        $this->placeGift($gift);

        return $this;
    }

    private function hasGift() : bool
    {
        return $this->gift !== null;
    }

    public function currentPlayer() : ?Player
    {
        return $this->currentPlayer;
    }

    /**
     * @return $this
     */
    public function withCurrentPlayer(?Player $player) : self
    {
        $this->currentPlayer = $player;

        return $this;
    }

    /**
     * @return $this
     */
    public function withMove(int $move) : self
    {
        $this->move = $move;

        return $this;
    }

    /**
     * @return $this
     */
    public function withNoCardsInARow(int $count) : self
    {
        $this->noCardsInARow = $count;

        return $this;
    }

    /**
     * @return $this
     */
    public function withShowPlayersLine(int $show) : self
    {
        $this->showPlayersLine = $show;

        return $this;
    }

    public static function maxPlayers(): int
    {
        return 10;
    }

    public function isFinished() : bool
    {
        return $this->isStarted() && ($this->hasWinner() || $this->isDraw());
    }

    /**
     * Starts the game ensuring that the deck is in place.
     */
    public function start() : MessageInterface
    {
        if (!$this->hasDeck()) {
            $deckFactory = new FullDeckFactory();
            $deck = $deckFactory->make()->shuffle();

            $this->withDeck($deck);
        }

        $message = parent::start();

        $this->currentPlayer = $this->starter();
        $this->move = 1;

        return $message;
    }

    public function runTillBreak() : MessageCollection
    {
        return $this->run(true);
    }

    /**
     * @param boolean $withBreak Should the game break and wait for the human interaction?
     */
    public function run(bool $withBreak = false) : MessageCollection
    {
        $messages = [];

        while (
            !$this->isFinished()
            && (!$withBreak
                || $this->canAutoMove($this->currentPlayer)
            )
        ) {
            $messages[] = $this->makeMove($this->currentPlayer);

            if (!$this->isFinished()) {
                $this->goToNextPlayer();
            }
        }

        if ($this->hasWinner()) {
            $messages[] = $this->winMessageFor(
                $this->currentPlayer
            );
        }

        if ($this->isDraw()) {
            $messages[] = new TextMessage(
                $this->drawReason(),
                'Ничья!'
            );
        }

        return MessageCollection::make($messages);
    }

    /**
     * Checks if the player can make a move in auto-mode (by the AI).
     * 
     * Auto-move is possible when:
     * 
     * - The player is a bot.
     * - The player has to skip a move and (optionally) do some pre-defined actions
     * (e.g., draw cards).
     */
    private function canAutoMove(Player $player) : bool
    {
        if ($player->isBot()) {
            return true;
        }

        return $this->isNextMoveASkip();
    }

    private function isNextMoveASkip() : bool
    {
        return $this->hasGift()
            && ($this->gift instanceof SkipActionInterface);
    }

    public function goToNextPlayer() : void
    {
        $this->currentPlayer = $this->nextPlayer($this->currentPlayer);
        $this->move++;
    }

    private function winMessageFor(Player $player) : MessageInterface
    {
        return new TextMessage(
            $player->equals($this->observer())
                ? $player->personalName() . ' выиграли!'
                : $this->parser()->parse($player, $player . ' выигра{л|ла}!')
        );
    }

    protected function dealing() : MessageInterface
    {
        $count = $this->players()->count();

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
            $this->cases()->caseForNumber('карта', $amount)
        );

        $cards = $this->drawToDiscard();

        Assert::notEmpty($cards);

        $message->appendLines(
            'Кладем ' . $cards . ' из колоды на стол'
        );

        $events = $this->giftAnnouncementEvents();

        if ($events->any()) {
            $accum = new CardEventAccumulator(...$events);

            $message->appendLines(
                ...$accum->messagesFor($this->observer())
            );
        }

        return $message;
    }

    public function makeMove(Player $player) : MessageInterface
    {
        Assert::true($this->isValidPlayer($player));
        Assert::true($this->isStarted());

        $moveStatus = $this->statusString();

        $moveMessages = $this
            ->actualMove($player)
            ->messagesFor($this->observer());

        $message = new TextMessage(
            $moveStatus,
            ...$moveMessages
        );

        if ($this->showPlayersLine) {
            $message->appendLines(
                $this->players()->handsString()
            );
        }

        return $message;
    }

    public function statusString() : string
    {
        return
            '[' . $this->move . '] ' .
            'Стол: ' . $this->discard()->topString() . ', ' .
            'Колода: ' . $this->deckSize();
    }

    private function actualMove(Player $player) : CardEventAccumulator
    {
        $events = new CardEventAccumulator();

        $gift = $this->retrieveGift();

        if ($gift instanceof ApplicableActionInterface) {
            $giftEvents = $gift->applyTo($this, $player);
            $events->addMany($giftEvents);
        }

        if ($events->hasSkip()) {
            return $events;
        }

        // drawing & trying to put a card
        while (true) {
            $chosenCard = $this->chooseCardToPut($player);

            if ($chosenCard) {
                $events->addMany(
                    $this->putCard($player, $chosenCard)
                );

                break;
            }

            if ($this->isDeckEmpty()) {
                $events->add(
                    $this->hasNoCardsToPut($player)
                );

                break;
            }

            $drawEvent = $this->drawToHand($player);

            if ($drawEvent) {
                $events->add($drawEvent);
            }
        }

        return $events;
    }

    /**
     * This function must be called in case when the player has no cards to put.
     */
    public function hasNoCardsToPut(Player $player) : CardEventInterface
    {
        $this->noCardsInARow++;

        return new NoCardsEvent($player);
    }

    /**
     * Todo: extract this to strategy
     */
    private function chooseCardToPut(Player $player) : ?Card
    {
        return $this
            ->getPlayableCardsFor($player)
            ->random();
    }

    public function putCard(Player $player, Card $card) : CardEventCollection
    {
        $events = new CardEventAccumulator();

        $this->discardFromHand($player, $card);

        $events->add(
            new DiscardEvent($player, $card)
        );

        // add gift's announcement events, if there is no winner yet
        // in case of a winner, gifts don't make sense
        if (!$this->hasWinner()) {
            $events->addMany(
                $this->giftAnnouncementEvents()
            );
        }

        $this->noCardsInARow = 0;

        return $events->events();
    }

    /**
     * Returns the currently playable cards for the player.
     */
    public function getPlayableCardsFor(Player $player) : CardCollection
    {
        Assert::true($this->isValidPlayer($player));

        return $player
            ->hand()
            ->cards()
            ->where(
                fn (Card $c) => $this->canBeDiscarded($c)
            );
    }

    protected function onDiscard(Card $card, ?Player $player = null) : void
    {
        $this->placeGift(
            $this->toGift($card, $player)
        );
    }

    protected function placeGift(?GiftAction $gift) : void
    {
        $this->gift = $gift;
    }

    protected function retrieveGift() : ?GiftAction
    {
        $gift = $this->gift;
        $this->gift = null;

        return $gift;
    }

    protected function giftAnnouncementEvents() : CardEventCollection
    {
        return $this->gift
            ? $this->gift->announcementEvents()
            : CardEventCollection::empty();
    }

    private function toGift(Card $card, ?Player $player = null) : ?GiftAction
    {
        if (!($card instanceof SuitedCard)) {
            return null;
        }

        // 6

        if ($card->isRank(Rank::six())) {
            return new SixGiftAction($card, $player);
        }

        // 7

        if ($card->isRank(Rank::seven())) {
            return new SevenGiftAction($card, $player);
        }

        // jack

        if ($card->isRank(Rank::jack())) {
            return new JackGiftAction($card, $player);
        }

        // 8

        if ($card->isRank(Rank::eight())) {
            // todo: add restriction on discard, not here
            // get suit for action from restriction
            $suit = $player
                ? $this->chooseSuit($player)
                : $card->suit();

            $action = new EightGiftAction($card, $suit, $player);

            $card->withRestriction(
                $action->restriction()
            );

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
        if (!$this->isStarted()) {
            return null;
        }

        return $this->players()->first(
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
        if ($this->move >= $this->maxMoves) {
            return 'Превышено максимальное число ходов (' . $this->maxMoves . '), что-то явно не так!';
        }

        if ($this->noCardsInARow >= $this->players()->count()) {
            return 'Ни у кого из игроков нет карт для хода';
        }

        return null;
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            [
                'gift' => $this->gift,
                'current_player_id' => $this->currentPlayer
                    ? $this->currentPlayer->id()
                    : null,
                'move' => $this->move,
                'no_cards_in_a_row' => $this->noCardsInARow,
                'show_players_line' => $this->showPlayersLine,
            ]
        );
    }
}
