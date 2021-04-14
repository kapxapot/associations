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
    private const ASSAULT_BEAR = 19;
    private const CLIMB_TREE = 20;
    private const RUN_AWAY = 21;
    private const BEAR_SCARED = 22;
    private const BEAR_NOT_SCARED = 23;
    private const TREE_JUMP = 24;
    private const KICK_BEAR = 25;
    private const ON_A_TREE = 26;
    private const KICK_SUCCESS = 27;
    private const KICK_FAIL = 28;
    private const GUMMY_BEARS = 29;

    public function __construct(
        int $id
    )
    {
        parent::__construct($id, '🌲 Лес', true);
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
                    'Вы гуляли по 🌲 <b>лесу</b> и заблудились. 😮'
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
            (new FinishNode(
                self::GENERIC_DEATH,
                [
                    'Вы умерли. 💀'
                ]
            ))->do(
                fn (WoodData $d) => $d->kill()
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::WANDERING,
                [
                    'Вы долго бродили по 🌲 <b>лесу</b> в поисках выхода.'
                ],
                [
                    new RedirectLink(self::FELL_IN_PIT),
                    new RedirectLink(self::FOUND_BERRIES),
                    new RedirectLink(self::FOUND_MUSHROOMS),
                    new RedirectLink(self::MET_BEAR),
                    new RedirectLink(self::GUMMY_BEARS, 0.3),
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
                    'Засмотревшись на красивые виды 🏞, вы случайно упали в глубокую 🕳 <b>яму</b> и сильно ударились. 🤕'
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
                    'С трудом выбравшись из 🕳 <b>ямы</b>, вы добрались 👣 до ближайшей поляны.'
                ],
                self::CLEARING
            )
        );

        $this->addNode(
            new ActionNode(
                self::FOUND_BERRIES,
                [
                    'Вы нашли какие-то неизвестные 🍒 <b>ягоды</b>.'
                ],
                [
                    self::EAT_BERRIES => '🍒 Есть',
                    self::CLEARING => '❌ Не есть'
                ]
            )
        );

        $this->addNode(
            (new SkipNode(
                self::EAT_BERRIES,
                [
                    '🍒 <b>ягоды</b> оказались вкусными и питательными. Вскоре вы снова вышли 👣 на поляну.'
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
                    'Под деревом растут какие-то подозрительные 🍄 <b>грибы</b>.'
                ],
                [
                    self::EAT_MUSHROOMS => '🍄 Есть',
                    self::CLEARING => '❌ Не есть'
                ]
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::EAT_MUSHROOMS,
                [
                    'Вам стало нехорошо. 🤢',
                    'Зачем же вы ели 🍄 <b>мухоморы</b>?'
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
                    'Вы бесцельно слонялись по 🌲 <b>лесу</b> и снова вышли на поляну.'
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
                    'Вы нашли дорогу и выбрались из 🌲 <b>леса</b>.',
                    'Поздравляем! 🎉'
                ]
            )
        );

        $this->addNode(
            (new RedirectNode(
                self::STUMP_WEEPING,
                [
                    'Вы сели на <b>пенек</b>, проплакали 😭 весь день и уснули. 😴'
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
                    'Проснувшись, вы вспомнили, что заблудились в 🌲 <b>лесу</b>. 😕'
                ],
                self::CLEARING
            )
        );

        $this->addNode(
            new SkipNode(
                self::PUTIN_BITCHES,
                [
                    'Вас разбудила 👩‍👩‍👧‍👧 <b>избирательная комиссия</b>, которой понадобился ваш пенек. 🤔',
                    'Вам пришлось уйти. 👣'
                ],
                self::WANDERING
            )
        );

        $this->addNode(
            new SkipNode(
                self::EATEN_IN_SLEEP,
                [
                    'Пока вы спали, пришли 🐺 <b>волки</b> и загрызли вас.'
                ],
                self::GENERIC_DEATH
            )
        );

        $this->addNode(
            (new ActionNode(
                self::CLEARING,
                [
                    'Вы на поляне, где лишь пара деревьев и одинокий <b>пенек</b>.'
                ],
                [
                    self::STUMP_WEEPING => 'Сесть на пенек',
                    self::WANDERING => '👣 Искать выход'
                ]
            ))
        );

        $this->addNode(
            new ActionNode(
                self::MET_BEAR,
                [
                    'Вы встретили 🐻 <b>медведя</b>. Похоже, он настроен недружелюбно.',
                    'Вы можете попытаться напугать зверя, залезть на 🌲 <b>дерево</b> или убежать.'
                ],
                [
                    self::ASSAULT_BEAR => 'Напугать',
                    self::CLIMB_TREE => '🌲 Лезть на дерево',
                    self::RUN_AWAY => '🏃 Убежать'
                ]
            )
        );

        $this->addNode(
            new SimpleRedirectNode(
                self::ASSAULT_BEAR,
                [
                    'Вы подняли руки вверх и громко зарычали. Точнее, закричали. 😱'
                ],
                [
                    self::BEAR_SCARED => 3,
                    self::BEAR_NOT_SCARED => 1
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                self::BEAR_SCARED,
                [
                    'Это сработало! 🐻 <b>медведь</b> убрался восвояси.'
                ],
                self::AIMLESS_WANDER
            )
        );

        $this->addNode(
            new ActionNode(
                self::BEAR_NOT_SCARED,
                [
                    'Упс! 🐻 <b>медведь</b> все еще желает вами перекусить.',
                    'Что будем делать?'
                ],
                [
                    self::CLIMB_TREE => '🌲 Лезть на дерево',
                    self::RUN_AWAY => '🏃 Убежать'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                self::CLIMB_TREE,
                [
                    'Лезть на 🌲 <b>дерево</b> от 🐻 <b>медведя</b>? Точно?',
                    '🐻 <b>медведь</b> полез за вами!'
                ],
                self::ON_A_TREE
            )
        );

        $this->addNode(
            new ActionNode(
                self::ON_A_TREE,
                [
                    'Вы можете прыгнуть на другое 🌲 <b>дерево</b> или пнуть 🐻 <b>медведя</b>.'
                ],
                [
                    self::TREE_JUMP => 'Прыгнуть',
                    self::KICK_BEAR => 'Пнуть'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                self::TREE_JUMP,
                [
                    'Вам удалось перепрыгнуть на другое 🌲 <b>дерево</b>, но...',
                    '🐻 <b>медведь</b> прыгнул за вами! 😮'
                ],
                self::ON_A_TREE
            )
        );

        $this->addNode(
            new RedirectNode(
                self::KICK_BEAR,
                [
                    'Вы со всей силы пнули 🐻 <b>медведя</b>.'
                ],
                [
                    (new RedirectLink(self::KICK_SUCCESS))->if(
                        fn (WoodData $d) => $d->hasShoes()
                    ),
                    (new RedirectLink(self::KICK_FAIL))->if(
                        fn (WoodData $d) => !$d->hasShoes()
                    )
                ]
            )
        );

        $this->addNode(
            (new SkipNode(
                self::KICK_SUCCESS,
                [
                    '🐻 <b>медведь</b> схватил вашу 👟 <b>кроссовку</b> и скрылся в подлеске.',
                    'Спустя несколько минут вы спустились и быстро убежали.'
                ],
                self::AIMLESS_WANDER
            ))->do(
                fn (WoodData $d) => $d->removeShoe()
            )
        );

        $this->addNode(
            new SkipNode(
                self::KICK_FAIL,
                [
                    'У вас не осталось обуви, поэтому 🐻 <b>медведь</b> схватил вас за ногу и сбросил с 🌲 <b>дерева</b>.'
                ],
                self::GENERIC_DEATH
            )
        );

        $this->addNode(
            new ActionNode(
                self::RUN_AWAY,
                [
                    '🐻 <b>медведь</b> бежит за вами.',
                    'Медведи бегают довольно быстро и очень выносливы...'
                ],
                [
                    self::CLIMB_TREE => '🌲 Лезть на дерево',
                    self::RUN_AWAY => '🏃 Бежать дальше'
                ]
            )
        );

        $this->addNode(
            new SkipNode(
                self::GUMMY_BEARS,
                [
                    'Наступив на кочку, вы внезапно улетели в небеса. ☁',
                    'Вы увидели 🌲 <b>лес</b> как на ладони и узнали, где выход.',
                    'Упав с большой высоты, вы разбились. 💥',
                    'Последней вашей мыслью было <i>«Неужели мишки Гамми...»</i> 🤔'
                ],
                self::GENERIC_DEATH
            )
        );
    }
}
