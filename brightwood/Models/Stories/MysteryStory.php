<?php

namespace Brightwood\Models\Stories;

use App\Models\TelegramUser;
use Brightwood\Models\Data\MysteryData;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\SkipNode;

class MysteryStory extends Story
{
    public function __construct(
        int $id
    )
    {
        parent::__construct($id, '🏰 Тайная местность', true);
    }

    public function makeData(TelegramUser $tgUser, ?array $data = null) : MysteryData
    {
        return new MysteryData($data);
    }

    protected function build() : void
    {
        $this->setStartNode(
            new ActionNode(
                1,
                [
                    'Ты гуляешь с Эмили и Робертом. Внезапно Роберт говорит:',
                    '— Дальше не пойдем.'
                ],
                [
                    3 => 'Почему?'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                2,
                [
                    'Ты {пришел|пришла} домой. Исчезла твоя мама.'
                ],
                [
                    5 => 'Пойти в полицию',
                    6 => 'Пойти в тайную местность'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                3,
                [
                    'Роберт:',
                    '— Там тайная местность. Там исчезают люди.'
                ],
                [
                    2 => 'Пойти домой',
                    6 => 'Пойдем туда!'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                5,
                [
                    '— Лучше не идти, — говорит Роберт.'
                ],
                [
                    9 => 'Все равно пойти',
                    10 => 'Не идти!'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                6,
                [
                    'Ты предлагаешь Эмили и Роберту пойти в тайную местность.',
                    'Эмили не хочет идти.'
                ],
                [
                    7 => 'Трусиха!',
                    8 => 'Пусть не идет'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                7,
                [
                    'Эмили все-таки решает идти с вами.'
                ],
                10
            )
        );

        $this->addNode(
            new SkipNode(
                8,
                [
                    'Вы с Робертом идете в тайную местность.'
                ],
                11
            )
        );

        $this->addNode(
            new FinishNode(
                9,
                [
                    'Ты {проиграл|проиграла}... 🙁 Попробуй еще раз!'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                10,
                [
                    'Вы с Эмили и Робертом идете в тайную местность.'
                ],
                11
            )
        );

        $this->addNode(
            new ActionNode(
                11,
                [
                    'Спустя какое-то время вы подходите к мрачной крепости.'
                ],
                [
                    12 => 'Зайти внутрь'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                12,
                [
                    'Вы заходите внутрь крепости, повсюду разбросаны кости, видимо, это все, что осталось от пропавших людей. 😥',
                    'Вдруг вы видите огромного... <b>ДРАКОНА</b>!!! 🐉',
                    'Он увидел вас и собирается напасть! 🔥'
                ],
                [
                    13 => 'Бежать',
                    9 => 'Уворачиваться'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                13,
                [
                    'Вы прибежали в другую комнату. Там целый клад! 👑',
                    'Вокруг драгоценности, доспехи и оружие.',
                    'Из этой комнаты нет другого выхода — только назад.',
                    'Прежде, чем вернуться назад...'
                ],
                [
                    19 => 'Набрать золота',
                    14 => 'Взять мечи получше'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                14,
                [
                    'Ты {вернулся|вернулась} в комнату с драконом. Он тебя заметил и готовится к атаке.'
                ],
                [
                    15 => 'Напасть на дракона',
                    16 => 'Спрятаться за колонну'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                15,
                [
                    'Ты подбегаешь к дракону, чтобы ударить его по незащищенному брюху.',
                    'Но дракон уворачивается и <b>сжигает тебя</b>. 🔥💀'
                ],
                9
            )
        );

        $this->addNode(
            new SkipNode(
                16,
                [
                    'Дракон кидается на тебя, но ты уворачиваешься, и он ударяется в колонну.',
                    'Колонна рушится и падает на дракона.',
                    '<b>ДРАКОН ПОВЕРЖЕН!</b>'
                ],
                17
            )
        );

        $this->addNode(
            new ActionNode(
                17,
                [
                    'Теперь нужно решить, что делать с кладом'
                ],
                [
                    18 => 'Раздать семьям пропавших',
                    9 => 'Оставить себе'
                ]
            )
        );

        $this->addNode(
            new FinishNode(
                18,
                [
                    'Ура! Ты {выиграл|выиграла}!'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                19,
                [
                    'Вы набрали золота и еле двигаетесь.',
                    'Дракон с легкостью вас <b>сжигает</b>. 🔥💀'
                ],
                9
            )
        );
    }
}
