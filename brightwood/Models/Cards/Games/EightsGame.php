<?php

namespace Brightwood\Models\Cards\Games;

use Brightwood\Collections\Cards\RankCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;
use Brightwood\Models\Cards\Moves\Actions\GiftAction;
use Brightwood\Models\Cards\Moves\Actions\RestrictingGiftAction;
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
    private int $maxMoves = 100; // temp. safeguard

    private StoryParser $parser;
    private Cases $cases;

    /**
     * Gift from the previous player.
     */
    private ?GiftAction $gift = null;

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
                    [
                        $this->parser->parse($player, $player . ' {выиграл|выиграла}!')
                    ]
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
            $lines[] = $this->parser->parse($player, $player . ' уже {выиграл|выиграла}!');
        }

        $this->moves++;

        $lines[] =
            '[' . $this->moves . '] ' .
            'Стол: ' . $this->topDiscard() . ', Колода: ' . $this->deckSize();;

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

        while (true) {
            $putCard = $this->tryPutCard($player);

            if ($putCard) {
                $lines[] = $player . ' кладет ' . $putCard . ' на стол';

                $this->gift = $this->toGift($player, $putCard);

                break;
            }

            if ($this->isDeckEmpty()) {
                $lines[] = $player . ' пропускает ход (нет карт)';
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

        // 6, 7, jack -> just return a gift

        $giftRanks = RankCollection::make(
            [
                Rank::six(),
                Rank::seven(),
                Rank::jack()
            ]
        );

        if ($giftRanks->contains($card->rank())) {
            return new GiftAction($player, $card);
        }

        // 8?

        if (!$card->isRank(Rank::eight())) {
            return null;
        }

        $suit = $this->chooseSuit($player);

        return new RestrictingGiftAction(
            $player,
            $card,
            fn (Card $c) => ($c instanceof SuitedCard) && $c->isSuit($suit)
        );
    }

    private function chooseSuit(Player $player) : Suit
    {
        // todo: extract this to strategy

        $suited = $player->hand()->filterSuited();

        return $suited->any()
            ? $suited->random()
            : Suit::all()->random();
    }

    private function tryPutCard(Player $player) : ?Card
    {
        $topDiscard = $this->topDiscard();

        Assert::notNull($topDiscard);

        // todo: extract this to strategy

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
        if (($one instanceof Joker) || ($two instanceof Joker)) {
            return true;
        }

        if (!($one instanceof SuitedCard) || !($two instanceof SuitedCard)) {
            return false;
        }

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
