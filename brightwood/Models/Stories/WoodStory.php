<?php

namespace Brightwood\Models\Stories;

use Brightwood\Models\Data\WoodData;
use Brightwood\Models\Links\RedirectLink;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\RedirectNode;
use Brightwood\Models\Nodes\SimpleRedirectNode;
use Brightwood\Models\Nodes\SkipNode;

class WoodStory extends Story
{
    public function __construct(
        int $id
    )
    {
        parent::__construct($id, 'Лес');
    }

    public function makeData(?array $data = null) : WoodData
    {
        return new WoodData($data);
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
            (new RedirectNode(
                5,
                [
                    'Вы сели на пенек, проплакали весь день и уснули. 😴'
                ],
                [
                    (new RedirectLink(6, 3))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(7, 1))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(2, 1))->if(
                        fn (WoodData $d) => $d->isDead()
                    )
                ]
            ))->do(
                fn (WoodData $d) => $d->nextDay()
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
    }
}
