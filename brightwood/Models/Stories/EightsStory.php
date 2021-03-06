<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\SkipNode;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class EightsStory extends Story
{
    private const RULES_COMMAND = '/rules';
    private const DRAW_CARD_COMMAND = '🎴 Взять карту';
    private const NO_CARDS_COMMAND = '❌ Нет карт';
    private const QUIT_GAME_COMMAND = '🏃 Выйти';

    private const START = 1;
    private const TWO_PLAYERS = 2;
    private const THREE_PLAYERS = 3;
    private const FOUR_PLAYERS = 4;
    private const PLAYERS_NUMBER_CHOICE = 5;
    private const START_GAME = 6;
    private const FINISH_GAME = 7;
    private const AUTO_MOVES = 8;
    private const HUMAN_MOVE = 9;

    private RootDeserializerInterface $rootDeserializer;

    public function __construct(
        int $id,
        RootDeserializerInterface $rootDeserializer
    )
    {
        parent::__construct($id, '♠ Восьмерки (почти готово!)', true);

        $this->rootDeserializer = $rootDeserializer;
    }

    public function makeData(?array $data = null) : EightsData
    {
        if ($data !== null) {
            try {
                return $this->rootDeserializer->deserialize($data);
            } catch (\InvalidArgumentException $ex) {
                // just ignore it
                // this is needed for parsing the data without the type
            }
        }

        return new EightsData($data);
    }

    public function executeCommand(string $command) : StoryMessageSequence
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

    protected function build() : void
    {
        $this->setStartNode(
            new SkipNode(
                self::START,
                [
                    '♠♥♣♦',
                    'Добро пожаловать в карточную игру <b>«Восьмерки»</b>!',
                    '📚 Правила: ' . self::RULES_COMMAND,
                    'На данный момент доступна игра только с 🤖 ботами.'
                ],
                self::PLAYERS_NUMBER_CHOICE
            )
        );

        $this->addNode(
            new ActionNode(
                self::PLAYERS_NUMBER_CHOICE,
                [
                    'Выберите количество игроков:'
                ],
                [
                    self::TWO_PLAYERS => '2',
                    self::THREE_PLAYERS => '3',
                    self::FOUR_PLAYERS => '4'
                ]
            )
        );

        $this->addNode(
            (new SkipNode(
                self::TWO_PLAYERS,
                [],
                self::START_GAME
            ))->do(
                fn (EightsData $d) => $d->withPlayerCount(2)
            )
        );

        $this->addNode(
            (new SkipNode(
                self::THREE_PLAYERS,
                [],
                self::START_GAME
            ))->do(
                fn (EightsData $d) => $d->withPlayerCount(3)
            )
        );

        $this->addNode(
            (new SkipNode(
                self::FOUR_PLAYERS,
                [],
                self::START_GAME
            ))->do(
                fn (EightsData $d) => $d->withPlayerCount(4)
            )
        );

        $this->addNode(
            new FunctionNode(
                self::START_GAME,
                function (TelegramUser $tgUser, EightsData $data, ?string $text = null) {
                    $data->initGame($tgUser);

                    $game = $data->game();
                    $players = $game->players();

                    return
                        StoryMessageSequence::make(
                            new StoryMessage(
                                self::AUTO_MOVES,
                                ['Играют:', Text::join($players->toArray())]
                            ),
                            $game->start()
                        )
                        ->withData($data);
                }
            )
        );

        $this->addNode(
            new FunctionNode(
                self::AUTO_MOVES,
                function (TelegramUser $tgUser, EightsData $data, ?string $text = null) {
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
            )
        );

        $this->addNode(
            new FunctionNode(
                self::HUMAN_MOVE,
                function (TelegramUser $tgUser, EightsData $data, ?string $text = null) {
                    $sequence = StoryMessageSequence::empty();

                    if ($text === self::QUIT_GAME_COMMAND) {
                        return $sequence->add(
                            new StoryMessage(
                                self::FINISH_GAME,
                                ['Вы покинули игру']
                            )
                        )->withData($data);
                    }

                    $game = $data->game();
                    $player = $this->getAndCheckPlayer($game, $tgUser);
                    $playableCards = $game->getPlayableCardsFor($player);

                    // play a card if it's valid
                    if (strlen($text) > 0 && $playableCards->any()) {
                        $card = Card::tryParse($text);

                        if (!$playableCards->contains($card)) {
                            $sequence->add(
                                new TextMessage('У вас нет такой карты. Вы что, шуле{р|рка}? 🤔')
                            );
                        } else {
                            $events = $game->putCard($player, $card);
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
                    if ($text === self::DRAW_CARD_COMMAND && !$game->isDeckEmpty()) {
                        $event = $game->drawToHand($player);

                        Assert::notNull($event);
                    }

                    // no cards?
                    if ($text === self::NO_CARDS_COMMAND && $game->isDeckEmpty()) {
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
                                [
                                    $game->statusString(),
                                    $game->players()->except($player)->handsString(),
                                    'Ваши карты: ' . $player->hand()
                                ]
                            ),
                            $playableCards->any()
                                ? new StoryMessage(
                                    0,
                                    ['Ваш ход:'],
                                    [
                                        ...$playableCards->stringize()->toArray(),
                                        self::QUIT_GAME_COMMAND
                                    ]
                                )
                                : ($game->isDeckEmpty()
                                    ? new StoryMessage(
                                        0,
                                        ['Вам нечем ходить, и колода пуста...'],
                                        [self::NO_CARDS_COMMAND, self::QUIT_GAME_COMMAND]
                                    )
                                    : new StoryMessage(
                                        0,
                                        ['Вам нечем ходить, берите карту 👇'],
                                        [self::DRAW_CARD_COMMAND, self::QUIT_GAME_COMMAND]
                                    )
                                )
                        )
                        ->withData($data);
                }
            )
        );

        $this->addNode(
            new FinishNode(
                self::FINISH_GAME,
                []
            )
        );
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
