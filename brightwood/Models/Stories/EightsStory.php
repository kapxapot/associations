<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Collections\MessageCollection;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\SkipNode;
use Plasticode\Util\Text;

class EightsStory extends Story
{
    private const RULES_COMMAND = '/rules';

    private const START = 1;
    private const TWO_PLAYERS = 2;
    private const THREE_PLAYERS = 3;
    private const FOUR_PLAYERS = 4;
    private const PLAYERS_NUMBER_CHOICE = 5;
    private const START_GAME = 6;
    private const FINISH_GAME = 7;
    private const NEXT_MOVE = 8;

    public function __construct(
        int $id
    )
    {
        parent::__construct($id, '♠ Восьмерки');
    }

    public function makeData(TelegramUser $tgUser, ?array $data = null) : EightsData
    {
        return new EightsData($tgUser, $data);
    }

    public function executeCommand(string $command) : MessageCollection
    {
        switch ($command) {
            case self::RULES_COMMAND:
                return MessageCollection::collect(
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
                fn (EightsData $d) => $d->setPlayerCount(2)
            )
        );

        $this->addNode(
            (new SkipNode(
                self::THREE_PLAYERS,
                [],
                self::START_GAME
            ))->do(
                fn (EightsData $d) => $d->setPlayerCount(3)
            )
        );

        $this->addNode(
            (new SkipNode(
                self::FOUR_PLAYERS,
                [],
                self::START_GAME
            ))->do(
                fn (EightsData $d) => $d->setPlayerCount(4)
            )
        );

        $this->addNode(
            new FunctionNode(
                self::START_GAME,
                function (EightsData $data) {
                    $data = $data->start();
                    $game = $data->game();
                    $players = $game->players();

                    $startMessage = new StoryMessage(
                        self::NEXT_MOVE,
                        [
                            'Играют:',
                            Text::join(
                                $players->toArray()
                            )
                        ]
                    );

                    $log = $game->run();

                    $sequence = new StoryMessageSequence($startMessage);
                    $sequence->add(...$log);

                    return $sequence->withData($data);
                }
            )
        );

        $this->addNode(
            new FinishNode(
                self::NEXT_MOVE,
                []
            )
        );
    }
}
