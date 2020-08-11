<?php

namespace App\Models\Brightwood\Stories;

use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
use App\Models\Brightwood\Nodes\RedirectNode;
use App\Models\Brightwood\Nodes\SkipNode;

class DemoStory extends Story
{
    protected function build() : void
    {
        $this->setStartNode(
            new ActionNode(
                1,
                'Вы гуляли по лесу 🌲🌲🌲 и заблудились. 😮',
                [
                    5 => 'Сесть на пенек и заплакать',
                    3 => 'Попытаться найти выход'
                ]
            )
        );

        $this->addNode(
            new FinishNode(2, 'Вы умерли от <b>голода</b>. 💀')
        );

        $this->addNode(
            new RedirectNode(
                3,
                'Вы долго бродили по лесу 🌲🌲🌲 в поисках выхода.',
                [
                    1 => 4,
                    4 => 1
                ]
            )
        );

        $this->addNode(
            new FinishNode(4, 'Вы нашли дорогу и выбрались из леса. 🎉🎉🎉')
        );

        $this->addNode(
            new RedirectNode(
                5,
                'Вы сели на пенек, проплакали весь день и уснули. 😴',
                [
                    6 => 3,
                    7 => 1,
                    2 => 1
                ]
            )
        );

        $this->addNode(
            new ActionNode(
                6,
                'Проснувшись, вы обнаружили, что вы все еще не знаете, где выход из леса. 😕',
                [
                    5 => 'Сесть на пенек и заплакать',
                    3 => 'Попытаться найти выход'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                7,
                'Вас разбудила <b>избирательная комиссия</b> 👩‍👩‍👧‍👧, которой понадобился ваш пенек. 🤔 Вам пришлось уйти.',
                1
            )
        );
    }
}
