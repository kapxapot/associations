<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Language;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Brightwood\StoryBuilder;
use InvalidArgumentException;
use Plasticode\Util\Cases;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class EightsStory extends Story
{
    const ID = 3;
    const TITLE = '♠ Карточная игра «Восьмерки»';
    const DESCRIPTION = 'Простая карточная игра с ботами. Сложность: 3/5';

    private const RULES_COMMAND = '/rules';
    private const DRAW_CARD_COMMAND = '🎴 Взять карту';
    private const NO_CARDS_COMMAND = '❌ Нет карт';

    private const START = 1;
    private const TWO_PLAYERS = 2;
    private const THREE_PLAYERS = 3;
    private const FOUR_PLAYERS = 4;
    private const PLAYERS_NUMBER_CHOICE = 5;
    private const START_GAME = 6;
    private const FINISH_GAME = 7;
    private const AUTO_MOVES = 8;
    private const HUMAN_MOVE = 9;
    private const SUIT_CHOICE = 10;

    private RootDeserializerInterface $rootDeserializer;
    private Cases $cases;

    private bool $drawing = false;

    public function __construct(
        RootDeserializerInterface $rootDeserializer,
        Cases $cases
    )
    {
        parent::__construct([
            'id' => self::ID,
            'lang_code' => Language::RU,
        ]);

        $this->rootDeserializer = $rootDeserializer;
        $this->cases = $cases;

        $this->title = self::TITLE;
        $this->description = self::DESCRIPTION;

        $this->prepare();
    }

    public function makeData(?array $data = null): EightsData
    {
        if ($data !== null) {
            try {
                return $this->rootDeserializer->deserialize($data);
            } catch (InvalidArgumentException $ex) {
                throw $ex;
                // ??
                // just ignore it
                // this is needed for parsing a data without a type
            }
        }

        return new EightsData($data);
    }

    public function executeCommand(string $command): StoryMessageSequence
    {
        switch ($command) {
            case self::RULES_COMMAND:
                return new StoryMessageSequence(
                    new TextMessage(
                        '<b>Правила игры в «Восьмерки»</b>',
                        'Колода: 52 карты + 2 джокера',
                        'Число игроков: 2-10 (оптимально 2-4)',
                        'Игрокам раздается по 7 карт — для двух игроков, по 5 — для трех и по 4, если игроков 4 и больше.',
                        'Одна карта из колоды сдается на стол в открытую, остальная колода кладется рубашкой вверх.',
                        'Игроки должны по очереди класть на стол карту той же масти или того же достоинства, что и карта на столе.',
                        'Например, если на столе лежит ♦4, игрок может положить четверку или <b>♦ бубны</b>.',
                        'Если у игрока нет подходящей карты, он берет карты из колоды, пока не найдет подходящую.',
                        'Если в колоде не осталось карт, игрок пропускает ход.'
                    ),
                    new TextMessage(
                        '<i>Особые карты</i>',
                        '- <b>8 (восьмерка):</b> Восьмерку можно класть на любую карту. При этом игрок называет масть, которую обязан положить следующий игрок.',
                        '- <b>🃏 (джокер):</b> Джокера тоже можно класть на любую карту. При этом считается, что на столе лежит карта, которая под джокером. Если под джокером ничего нет, на него можно класть любую карту.',
                        '- <b>J (валет):</b> Следующий игрок пропускает ход.',
                        '- <b>6 (шестерка):</b> Следующий игрок берет из колоды 1 карту и пропускает ход.',
                        '- <b>7 (семерка):</b> Следующий игрок берет из колоды 2 карты и пропускает ход.'
                    ),
                    new TextMessage(
                        '<i>Завершение игры</i>',
                        'Победителем считается первый игрок, выложивший все карты из руки.',
                        'Если всем игрокам нечего положить на стол, игра заканчивается ничьей.'
                    ),
                    new TextMessage(
                        '<i>Базовая стратегия</i>',
                        'Целью игры является сбросить свои карты как можно быстрее, при этом сделать так, чтобы другие игроки набрали как можно больше карт.'
                    )
                );
        }

        return parent::executeCommand($command);
    }

    protected function build(): void
    {
        $builder = new StoryBuilder($this);

        $start = $builder->addSkipNode(
            self::START,
            self::PLAYERS_NUMBER_CHOICE,
            [
                Suit::all()->join(),
                'Добро пожаловать в карточную игру <b>«Восьмерки»</b>!',
                '📚 Правила: ' . self::RULES_COMMAND,
                'На данный момент доступна игра только с 🤖 ботами.',
            ]
        );

        $this->setStartNode($start);

        $builder->addActionNode(
            self::PLAYERS_NUMBER_CHOICE,
            'Выберите количество игроков:',
            [
                self::TWO_PLAYERS => '2',
                self::THREE_PLAYERS => '3',
                self::FOUR_PLAYERS => '4',
            ]
        );

        $builder
            ->addSkipNode(self::TWO_PLAYERS, self::START_GAME)
            ->does(
                fn (EightsData $d) => $d->withPlayerCount(2)
            );

        $builder
            ->addSkipNode(self::THREE_PLAYERS, self::START_GAME)
            ->does(
                fn (EightsData $d) => $d->withPlayerCount(3)
            );

        $builder
            ->addSkipNode(self::FOUR_PLAYERS, self::START_GAME)
            ->does(
                fn (EightsData $d) => $d->withPlayerCount(4)
            );

        $builder->addFunctionNode(self::START_GAME, [$this, 'startGame']);
        $builder->addFunctionNode(self::AUTO_MOVES, [$this, 'autoMoves']);
        $builder->addFunctionNode(self::HUMAN_MOVE, [$this, 'humanMove']);
        $builder->addFunctionNode(self::SUIT_CHOICE, [$this, 'suitChoice']);

        $builder->addFinishNode(self::FINISH_GAME);
    }

    public function startGame(
        TelegramUser $tgUser,
        EightsData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $data->initGame($tgUser);

        $game = $data->game();
        $players = $game->players();

        return
            StoryMessageSequence::make(
                new StoryMessage(
                    self::AUTO_MOVES,
                    ['Играют:', Text::join($players)]
                ),
                $game->start()
            )
            ->withData($data);
    }

    public function autoMoves(
        TelegramUser $tgUser,
        EightsData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $game = $data->game();

        $sequence = new StoryMessageSequence(
            ...$game->runTillBreak()
        );

        if ($game->isFinished()) {
            return $sequence
                ->add(new StoryMessage(self::FINISH_GAME))
                ->withData($data);
        }

        // the player isn't needed, but the check is needed
        $this->getAndCheckPlayer($game, $tgUser);

        return $sequence
            ->add(new StoryMessage(self::HUMAN_MOVE))
            ->withData($data);
    }

    public function humanMove(
        TelegramUser $tgUser,
        EightsData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $sequence = StoryMessageSequence::empty();

        $beenDrawing = $this->drawing;
        $this->drawing = false;

        $game = $data->game();
        $player = $this->getAndCheckPlayer($game, $tgUser);
        $playableCards = $game->getPlayableCardsFor($player);

        // play a card if it's valid
        if (strlen($input) > 0 && $playableCards->any()) {
            $card = Card::tryParse($input);

            if (!$playableCards->contains($card)) {
                $sequence->addText('У вас нет такой карты. Вы что, шулер{|ка}? 🤔');
            } else {
                $events = $game->putCard($player, $card);

                // for eight go to suit choice
                if ($card->isRank(Rank::eight())) {
                    return $sequence
                        ->add(
                            new StoryMessage(self::SUIT_CHOICE)
                        )
                        ->withData($data);
                }

                // otherwise go to next player
                $game->goToNextPlayer();

                return $sequence
                    ->add(
                        new StoryMessage(
                            self::AUTO_MOVES,
                            $events->messagesFor($player)->toArray()
                        )
                    )
                    ->withData($data);
            }
        }

        /** @var CardEventInterface */
        $event = null;

        // draw a card?
        if ($input === self::DRAW_CARD_COMMAND && !$game->isDeckEmpty()) {
            $event = $game->drawToHand($player);
            Assert::notNull($event);
            $this->drawing = true;
        }

        // no cards?
        if ($input === self::NO_CARDS_COMMAND && $game->isDeckEmpty()) {
            $event = $game->hasNoCardsToPut($player);
            $game->goToNextPlayer();
        }

        if ($event) {
            return $sequence
                ->add(
                    new StoryMessage(
                        self::AUTO_MOVES,
                        [$event->messageFor($player)],
                    )
                )
                ->withData($data);
        }

        return $sequence
            ->add(
                new StoryMessage(
                    self::HUMAN_MOVE,
                    $beenDrawing && $playableCards->isEmpty()
                        ? []
                        : [
                            $game->statusString(),
                            Text::join(
                                $game->players()->except($player)->handsStrings()
                            ),
                            sprintf(
                                'У вас %s %s: %s',
                                $player->handSize(),
                                $this->cases->caseForNumber('карта', $player->handSize()),
                                $player->hand()
                                    ->sortReverse([EightsGame::class, 'sort'])
                                    ->toRuString()
                            )
                        ]
                ),
                $playableCards->any()
                    ? new StoryMessage(
                        0,
                        ['Ваш ход:'],
                        [...$playableCards
                            ->distinct()
                            ->sortReverse([EightsGame::class, 'sort'])
                            ->stringize(
                                fn (Card $c) => $c->name('ru')
                            )]
                    )
                    : ($game->isDeckEmpty()
                        ? new StoryMessage(
                            0,
                            ['Вам нечем ходить, и колода пуста...'],
                            [self::NO_CARDS_COMMAND]
                        )
                        : new StoryMessage(
                            0,
                            ['Вам нечем ходить, берите карту 👇'],
                            [self::DRAW_CARD_COMMAND]
                        )
                    )
            )
            ->withData($data);
    }

    public function suitChoice(
        TelegramUser $tgUser,
        EightsData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        $sequence = StoryMessageSequence::empty();

        $game = $data->game();
        $player = $this->getAndCheckPlayer($game, $tgUser);

        // choose a suit if it's valid
        if (strlen($input) > 0) {
            $suit = Suit::tryParse($input);

            if (!$suit) {
                $sequence->addText('Не понятно, попробуйте еще раз...');
            } else {
                // apply suit...
                $events = $game->playerChoosesEightSuit($player, $suit);

                $game->goToNextPlayer();

                return $sequence
                    ->add(
                        new StoryMessage(
                            self::AUTO_MOVES,
                            $events->messagesFor($player)->toArray()
                        )
                    )
                    ->withData($data);
            }
        }

        return $sequence
            ->add(
                new StoryMessage(
                    self::SUIT_CHOICE,
                    ['Выберите масть (следующий игрок должен положить карту этой масти):'],
                    [...Suit::all()->stringize()]
                )
            )
            ->withData($data);
    }

    private function getAndCheckPlayer(EightsGame $game, TelegramUser $tgUser) : Human
    {
        /** @var Human */
        $player = $game->currentPlayer();

        Assert::isInstanceOf($player, Human::class);
        Assert::true($player->telegramUser()->equals($tgUser));

        return $player;
    }
}
