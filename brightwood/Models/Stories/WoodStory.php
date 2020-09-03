<?php

namespace Brightwood\Models\Stories;

use Brightwood\Models\Data\WoodData;
use Brightwood\Models\Links\RedirectLink;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\RedirectNode;
use Brightwood\Models\Nodes\SkipNode;

class WoodStory extends Story
{
    private const START = 1;
    private const STARVED = 2;
    private const WANDERING = 3;
    private const EXIT = 4;
    private const STUMP_WEEPING = 5;
    private const CLEARING_WAKE_UP = 6;
    private const PUTIN_BITCHES = 7;
    private const EATEN_IN_SLEEP = 8;
    private const CLEARING = 9;
    private const FELL_IN_PIT = 10;
    private const FOUND_BERRIES = 11;
    private const FOUND_MUSHROOMS = 12;
    private const MET_BEAR = 13;
    private const GENERIC_DEATH = 14;
    private const GOT_OUT_OF_PIT = 15;
    private const EAT_BERRIES = 16;
    private const EAT_MUSHROOMS = 17;
    private const AIMLESS_WANDER = 18;

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
            new SkipNode(
                self::START,
                [
                    'Вы гуляли по лесу 🌲🌲🌲 и заблудились. 😮'
                ],
                self::CLEARING
            )
        );

        $this->addNode(
            new FinishNode(
                self::STARVED,
                [
                    'Вы умерли от <b>голода</b>. 💀'
                ]
            )
        );

        $this->addNode(
            new FinishNode(
                self::GENERIC_DEATH,
                [
                    'Вы умерли. 💀'
                ]
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::WANDERING,
                [
                    'Вы долго бродили по лесу 🌲🌲🌲 в поисках выхода.'
                ],
                [
                    new RedirectLink(self::FELL_IN_PIT),
                    new RedirectLink(self::FOUND_BERRIES),
                    new RedirectLink(self::FOUND_MUSHROOMS),
                    (new RedirectLink(self::EXIT))->if(
                        fn (WoodData $d) => $d->hasWanderedEnough()
                    )
                ]
            ))->do(
                fn (WoodData $d) => $d->wander()
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::FELL_IN_PIT,
                [
                    'Засмотревшись на красивые виды, вы случайно упали в глубокую <b>яму</b> 🕳 и сильно ударились.'
                ],
                [
                    (new RedirectLink(self::GOT_OUT_OF_PIT))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(self::GENERIC_DEATH))->if(
                        fn (WoodData $d) => $d->isDead()
                    )
                ]
            ))->do(
                fn (WoodData $d) => $d->hit(1)
            )
        );

        $this->addNode(
            new SkipNode(
                self::GOT_OUT_OF_PIT,
                [
                    'С трудом выбравшись из <b>ямы</b> 🕳, вы добрались до ближайшей поляны.'
                ],
                self::CLEARING
            )
        );

        $this->addNode(
            new ActionNode(
                self::FOUND_BERRIES,
                [
                    'Вы нашли какие-то неизвестные ягоды. 🍒'
                ],
                [
                    self::EAT_BERRIES => 'Есть',
                    self::CLEARING => 'Не есть'
                ]
            )
        );

        $this->addNode(
            (new SkipNode(
                self::EAT_BERRIES,
                [
                    'Ягоды оказались вкусными и питательными. Вскоре вы снова вышли на поляну.'
                ],
                self::CLEARING
            ))->do(
                fn (WoodData $d) => $d->heal(1)
            )
        );

        $this->addNode(
            new ActionNode(
                self::FOUND_MUSHROOMS,
                [
                    'Под деревом растут какие-то подозрительные грибы. 🍄'
                ],
                [
                    self::EAT_MUSHROOMS => 'Есть',
                    self::CLEARING => 'Не есть'
                ]
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::EAT_MUSHROOMS,
                [
                    'Вам стало нехорошо. Зачем же вы ели мухоморы? 🤢'
                ],
                [
                    (new RedirectLink(self::AIMLESS_WANDER))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(self::GENERIC_DEATH))->if(
                        fn (WoodData $d) => $d->isDead()
                    )
                ]
            ))->do(
                fn (WoodData $d) => $d->hit(1)
            )
        );

        $this->addNode(
            (new SkipNode(
                self::AIMLESS_WANDER,
                [
                    'Вы бесцельно слонялись по лесу и снова вышли на поляну.'
                ],
                self::CLEARING
            ))->do(
                fn (WoodData $d) => $d->wander()
            )
        );

        $this->addNode(
            new FinishNode(
                self::EXIT,
                [
                    'Вы нашли дорогу и выбрались из леса. 🎉🎉🎉'
                ]
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::STUMP_WEEPING,
                [
                    'Вы сели на пенек, проплакали весь день и уснули. 😴'
                ],
                [
                    (new RedirectLink(self::CLEARING_WAKE_UP, 2))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(self::PUTIN_BITCHES, 2))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(self::EATEN_IN_SLEEP, 1))->if(
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    (new RedirectLink(self::STARVED))->if(
                        fn (WoodData $d) => $d->isDead()
                    )
                ]
            ))->do(
                fn (WoodData $d) => $d->nextDay()
            )
        );

        $this->addNode(
            new SkipNode(
                self::CLEARING_WAKE_UP,
                [
                    'Проснувшись, вы вспомнили, что заблудились в лесу. 😕'
                ],
                self::CLEARING
            )
        );

        $this->addNode(
            new SkipNode(
                self::PUTIN_BITCHES,
                [
                    'Вас разбудила <b>избирательная комиссия</b> 👩‍👩‍👧‍👧, которой понадобился ваш пенек. 🤔 Вам пришлось уйти.'
                ],
                self::WANDERING
            )
        );

        $this->addNode(
            (new FinishNode(
                self::EATEN_IN_SLEEP,
                [
                    'Пока вы спали, пришли <b>волки</b> 🐺🐺🐺 и съели вас. 💀'
                ]
            ))->do(
                fn (WoodData $d) => $d->kill()
            )
        );

        $this->addNode(
            (new ActionNode(
                self::CLEARING,
                [
                    'Вы на поляне, где лишь пара деревьев и одинокий пень.'
                ],
                [
                    self::STUMP_WEEPING => 'Сесть на пенек и заплакать',
                    self::WANDERING => 'Попытаться найти выход'
                ]
            ))
        );
    }
}
