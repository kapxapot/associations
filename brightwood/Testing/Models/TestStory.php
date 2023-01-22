<?php

namespace Brightwood\Testing\Models;

use App\Models\TelegramUser;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\SimpleRedirectNode;
use Brightwood\Models\Nodes\SkipNode;
use Brightwood\Models\Stories\Story;

class TestStory extends Story
{
    public function __construct(
        int $id
    )
    {
        parent::__construct($id, 'Лес', 'Blah');
    }

    public function makeData(?array $data = null) : TestData
    {
        return new TestData($data);
    }

    protected function build() : void
    {
        $this->setMessagePrefix('День: {day}, Здоровье: {hp}');

        $this->setStartNode(
            new ActionNode(
                1,
                [
                    'Вы гуляли по лесу 🌲🌲🌲 и заблудились. 😮'
                ],
                [
                    5 => 'Сесть на пенек и заплакать',
                    3 => 'Попытаться найти выход'
                ]
            )
        );

        $this->addNode(
            new FinishNode(
                2,
                [
                    'Вы умерли от <b>голода</b>. 💀'
                ]
            )
        );

        $this->addNode(
            new SimpleRedirectNode(
                3,
                [
                    'Вы долго бродили по лесу 🌲🌲🌲 в поисках выхода.'
                ],
                [
                    1 => 4,
                    4 => 1
                ]
            )
        );

        $this->addNode(
            new FinishNode(
                4,
                [
                    'Вы нашли дорогу и выбрались из леса. 🎉🎉🎉'
                ]
            )
        );

        $this->addNode(
            (new SimpleRedirectNode(
                5,
                [
                    'Вы сели на пенек, проплакали весь день и уснули. 😴'
                ],
                [
                    6 => 3,
                    7 => 1,
                    2 => 1
                ]
            ))->do(
                fn (TestData $d) => $d->nextDay()
            )
        );

        $this->addNode(
            new ActionNode(
                6,
                [
                    'Проснувшись, вы обнаружили, что вы все еще не знаете, где выход из леса. 😕'
                ],
                [
                    5 => 'Сесть на пенек и заплакать',
                    3 => 'Попытаться найти выход'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                7,
                [
                    'Вас разбудила <b>избирательная комиссия</b> 👩‍👩‍👧‍👧, которой понадобился ваш пенек. 🤔 Вам пришлось уйти.'
                ],
                1
            )
        );

        $this->addNode(
            new FinishNode(
                8,
                []
            )
        );
    }
}
