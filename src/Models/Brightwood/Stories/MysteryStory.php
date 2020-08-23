<?php

namespace App\Models\Brightwood\Stories;

use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
use App\Models\Brightwood\Nodes\SkipNode;
use Plasticode\Util\Text;

class MysteryStory extends Story
{
    protected function build() : void
    {
        $this->setStartNode(
            new ActionNode(
                1,
                Text::sparseJoin(
                    [
                        'Ты гуляешь с Эмили и Робертом. Внезапно Роберт говорит:',
                        '— Дальше не пойдем.'
                    ]
                ),
                [
                    3 => 'Почему?'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                2,
                'Ты пришел/шла домой. Исчезла твоя мама.',
                [
                    5 => 'Пойти в полицию',
                    6 => 'Пойти в тайную местность'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                3,
                Text::sparseJoin(
                    [
                        'Роберт:',
                        '— Там тайная местность. Там исчезают люди.'
                    ]
                ),
                [
                    2 => 'Пойти домой',
                    6 => 'Пойдем туда!'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                5,
                '— Лучше не идти, — говорит Роберт.',
                [
                    9 => 'Все равно пойти в полицию',
                    10 => 'Не идти!'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                6,
                Text::sparseJoin(
                    [
                        'Ты предлагаешь Эмили и Роберту пойти в тайную местность.',
                        'Эмили не хочет идти.'
                    ]
                ),
                [
                    7 => 'Трусиха!',
                    8 => 'Пусть не идет'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                7,
                'Эмили все-таки решает идти с вами.',
                10
            )
        );

        $this->addNode(
            new SkipNode(
                8,
                'Вы с Робертом идете в тайную местность.',
                11
            )
        );

        $this->addNode(
            new FinishNode(9, 'ПРОИГРЫШ')
        );

        $this->addNode(
            new SkipNode(
                10,
                'Вы с Эмили и Робертом идете в тайную местность.',
                11
            )
        );

        $this->addNode(
            new ActionNode(
                11,
                'Спустя какое-то время вы подходите к мрачной крепости.',
                [
                    12 => 'Зайти внутрь'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                12,
                Text::sparseJoin(
                    [
                        'Вы заходите внутрь крепости, повсюду разбросаны кости, видимо, это все, что осталось от пропавших людей. 😥',
                        'Вдруг вы видите огромного... <b>ДРАКОНА</b>!!! 🐉',
                        'Он увидел вас и собирается напасть! 🔥'
                    ]
                ),
                [
                    13 => 'Бежать',
                    9 => 'Уворачиваться'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                13,
                Text::sparseJoin(
                    [
                        'Вы прибежали в комнату. Там целый клад! 👑',
                        'Вокруг драгоценности, доспехи и оружие.',
                        'Из этой комнаты нет другого выхода — только назад.'
                    ]
                ),
                [
                    9 => 'Набрать побольше золота и выбежать',
                    14 => 'Взять мечи хорошего качества и выбежать'
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                14,
                'Ты вернулся/лась в комнату с драконом. Он тебя заметил и готовится к атаке.',
                [
                    15 => 'Напасть на дракона',
                    16 => 'Спрятаться за колонну'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                15,
                Text::sparseJoin(
                    [
                        'Ты подбегаешь к дракону, чтобы ударить его по незащищенному брюху.',
                        'Но дракон уворачивается и <b>сжигает тебя</b>. 🔥💀'
                    ]
                ),
                9
            )
        );

        $this->addNode(
            new SkipNode(
                16,
                Text::sparseJoin(
                    [
                        'Дракон кидается на тебя, но ты уворачиваешься, и он ударяется в колонну.',
                        'Колонна рушится и падает на дракона.',
                        '<b>ДРАКОН ПОВЕРЖЕН!</b>'
                    ]
                ),
                17
            )
        );

        $this->addNode(
            new ActionNode(
                17,
                'Теперь нужно решить, что делать с кладом',
                [
                    18 => 'Раздать семьям пропавших',
                    9 => 'Оставить себе'
                ]
            )
        );

        $this->addNode(
            new FinishNode(18, 'Ура! Ты выиграл(а)!')
        );
    }
}
