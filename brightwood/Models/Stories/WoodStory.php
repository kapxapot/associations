<?php

namespace Brightwood\Models\Stories;

use Brightwood\Models\Data\WoodData;
use Brightwood\Models\Language;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\StoryBuilder;

class WoodStory extends Story
{
    const ID = 1;
    const TITLE = '🌲 Лес';
    const DESCRIPTION = 'Вы заблудились в лесу и пытаетесь из него выбраться. Или не пытаетесь. Сложность: 3/5';

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

    public function __construct()
    {
        parent::__construct([
            'id' => self::ID,
            'lang_code' => Language::RU,
        ]);

        $this->title = self::TITLE;
        $this->description = self::DESCRIPTION;

        $this->prepare();
    }

    public function newData(): WoodData
    {
        return new WoodData();
    }

    public function loadData(array $data): WoodData
    {
        return new WoodData($data);
    }

    protected function build(): void
    {
        $builder = new StoryBuilder($this);

        $this->setPrefixMessage('День: {day}, Здоровье: {hp}');

        $start = $builder->addSkipNode(
            self::START,
            self::CLEARING,
            'Вы гуляли по 🌲 <b>лесу</b> и заблудились. 😮',
        );

        $this->setStartNode($start);

        $builder->addFinishNode(
            self::STARVED,
            'Вы умерли от <b>голода</b>. 💀'
        );

        $builder
            ->addFinishNode(
                self::GENERIC_DEATH,
                'Вы умерли. 💀'
            )
            ->does(
                fn (WoodData $d) => $d->kill()
            );

        $builder
            ->addRedirectNode(
                self::WANDERING,
                'Вы долго бродили по 🌲 <b>лесу</b> в поисках выхода.',
                [
                    self::FELL_IN_PIT,
                    self::FOUND_BERRIES,
                    self::FOUND_MUSHROOMS,
                    self::MET_BEAR,
                    [self::GUMMY_BEARS, 0.3],
                    $builder->redirectsIf(
                        self::EXIT,
                        fn (WoodData $d) => $d->hasWanderedEnough()
                    ),
                ]
            )
            ->does(
                fn (WoodData $d) => $d->wander()
            );

        $builder
            ->addRedirectNode(
                self::FELL_IN_PIT,
                'Засмотревшись на красивые виды 🏞, вы случайно упали в глубокую 🕳 <b>яму</b> и сильно ударились. 🤕',
                [
                    $builder->redirectsIf(
                        self::GOT_OUT_OF_PIT,
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    $builder->redirectsIf(
                        self::GENERIC_DEATH,
                        fn (WoodData $d) => $d->isDead()
                    ),
                ]
            )->does(
                fn (WoodData $d) => $d->hit(1)
            );

        $builder->addSkipNode(
            self::GOT_OUT_OF_PIT,
            self::CLEARING,
            'С трудом выбравшись из 🕳 <b>ямы</b>, вы добрались 👣 до ближайшей поляны.'
        );

        $builder->addActionNode(
            self::FOUND_BERRIES,
            'Вы нашли какие-то неизвестные 🍒 <b>ягоды</b>.',
            [
                self::EAT_BERRIES => '🍒 Есть',
                self::CLEARING => '❌ Не есть',
            ]
        );

        $builder
            ->addSkipNode(
                self::EAT_BERRIES,
                self::CLEARING,
                '🍒 <b>ягоды</b> оказались вкусными и питательными. Вскоре вы снова вышли 👣 на поляну.'
            )
            ->does(
                fn (WoodData $d) => $d->heal(1)
            );

        $builder->addActionNode(
            self::FOUND_MUSHROOMS,
            'Под деревом растут какие-то подозрительные 🍄 <b>грибы</b>.',
            [
                self::EAT_MUSHROOMS => '🍄 Есть',
                self::CLEARING => '❌ Не есть',
            ]
        );

        $builder
            ->addRedirectNode(
                self::EAT_MUSHROOMS,
                [
                    'Вам стало нехорошо. 🤢',
                    'Зачем же вы ели 🍄 <b>мухоморы</b>?',
                ],
                [
                    $builder->redirectsIf(
                        self::AIMLESS_WANDER,
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    $builder->redirectsIf(
                        self::GENERIC_DEATH,
                        fn (WoodData $d) => $d->isDead()
                    ),
                ]
            )
            ->does(
                fn (WoodData $d) => $d->hit(1)
            );

        $builder
            ->addSkipNode(
                self::AIMLESS_WANDER,
                self::CLEARING,
                'Вы бесцельно слонялись по 🌲 <b>лесу</b> и снова вышли на поляну.',
            )
            ->does(
                fn (WoodData $d) => $d->wander()
            );

        $builder->addFinishNode(
            self::EXIT,
            [
                'Вы нашли дорогу и выбрались из 🌲 <b>леса</b>.',
                'Поздравляем! 🎉',
            ]
        );

        $builder
            ->addRedirectNode(
                self::STUMP_WEEPING,
                'Вы сели на <b>пенек</b>, проплакали 😭 весь день и уснули. 😴',
                [
                    $builder->redirectsIf(
                        [self::CLEARING_WAKE_UP, 2],
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    $builder->redirectsIf(
                        [self::PUTIN_BITCHES, 2],
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    $builder->redirectsIf(
                        self::EATEN_IN_SLEEP,
                        fn (WoodData $d) => $d->isAlive()
                    ),
                    $builder->redirectsIf(
                        self::STARVED,
                        fn (WoodData $d) => $d->isDead()
                    ),
                ]
            )
            ->does(
                fn (WoodData $d) => $d->nextDay()
            );

        $builder->addSkipNode(
            self::CLEARING_WAKE_UP,
            self::CLEARING,
            'Проснувшись, вы вспомнили, что заблудились в 🌲 <b>лесу</b>. 😕',
        );

        $builder->addSkipNode(
            self::PUTIN_BITCHES,
            self::WANDERING,
            [
                'Вас разбудила 👩‍👩‍👧‍👧 <b>избирательная комиссия</b>, которой понадобился ваш пенек. 🤔',
                'Вам пришлось уйти. 👣',
            ],
        );

        $builder->addSkipNode(
            self::EATEN_IN_SLEEP,
            self::GENERIC_DEATH,
            'Пока вы спали, пришли 🐺 <b>волки</b> и загрызли вас.'
        );

        $builder->addActionNode(
            self::CLEARING,
            'Вы на поляне, где лишь пара деревьев и одинокий <b>пенек</b>.',
            [
                self::STUMP_WEEPING => 'Сесть на пенек',
                self::WANDERING => '👣 Искать выход',
            ]
        );

        $builder->addActionNode(
            self::MET_BEAR,
            [
                'Вы встретили 🐻 <b>медведя</b>. Похоже, он настроен недружелюбно.',
                'Вы можете попытаться напугать зверя, залезть на 🌲 <b>дерево</b> или убежать.',
            ],
            [
                self::ASSAULT_BEAR => 'Напугать',
                self::CLIMB_TREE => '🌲 Лезть на дерево',
                self::RUN_AWAY => '🏃 Убежать',
            ]
        );

        $builder->addRedirectNode(
            self::ASSAULT_BEAR,
            'Вы подняли руки вверх и громко зарычали. Точнее, закричали. 😱',
            [
                [self::BEAR_SCARED, 2],
                self::BEAR_NOT_SCARED,
            ]
        );

        $builder->addSkipNode(
            self::BEAR_SCARED,
            self::AIMLESS_WANDER,
            'Это сработало! 🐻 <b>медведь</b> убрался восвояси.'
        );

        $builder->addActionNode(
            self::BEAR_NOT_SCARED,
            [
                'Упс! 🐻 <b>медведь</b> все еще желает вами перекусить.',
                'Что будем делать?',
            ],
            [
                self::CLIMB_TREE => '🌲 Лезть на дерево',
                self::RUN_AWAY => '🏃 Убежать',
            ]
        );

        $builder->addSkipNode(
            self::CLIMB_TREE,
            self::ON_A_TREE,
            [
                'Лезть на 🌲 <b>дерево</b> от 🐻 <b>медведя</b>? Точно?',
                '🐻 <b>медведь</b> полез за вами!',
            ]
        );

        $builder->addActionNode(
            self::ON_A_TREE,
            'Вы можете прыгнуть на другое 🌲 <b>дерево</b> или пнуть 🐻 <b>медведя</b>.',
            [
                self::TREE_JUMP => 'Прыгнуть',
                self::KICK_BEAR => 'Пнуть',
            ]
        );

        $builder->addSkipNode(
            self::TREE_JUMP,
            self::ON_A_TREE,
            [
                'Вам удалось перепрыгнуть на другое 🌲 <b>дерево</b>, но...',
                '🐻 <b>медведь</b> прыгнул за вами! 😮',
            ]
        );

        $builder->addRedirectNode(
            self::KICK_BEAR,
            'Вы со всей силы пнули 🐻 <b>медведя</b>.',
            [
                $builder->redirectsIf(
                    self::KICK_SUCCESS,
                    fn (WoodData $d) => $d->hasShoes()
                ),
                $builder->redirectsIf(
                    self::KICK_FAIL,
                    fn (WoodData $d) => !$d->hasShoes()
                ),
            ]
        );

        $builder
            ->addSkipNode(
                self::KICK_SUCCESS,
                self::AIMLESS_WANDER,
                [
                    '🐻 <b>медведь</b> схватил вашу 👟 <b>кроссовку</b> и скрылся в подлеске.',
                    'Спустя несколько минут вы спустились и быстро убежали.',
                ]
            )
            ->does(
                fn (WoodData $d) => $d->removeShoe()
            );

        $builder->addSkipNode(
            self::KICK_FAIL,
            self::GENERIC_DEATH,
            'У вас не осталось обуви, поэтому 🐻 <b>медведь</b> схватил вас за ногу и сбросил с 🌲 <b>дерева</b>.'
        );

        $builder->addActionNode(
            self::RUN_AWAY,
            [
                '🐻 <b>медведь</b> бежит за вами.',
                'Медведи бегают довольно быстро и очень выносливы...',
            ],
            [
                self::CLIMB_TREE => '🌲 Лезть на дерево',
                self::RUN_AWAY => '🏃 Бежать дальше',
            ]
        );

        $builder->addSkipNode(
            self::GUMMY_BEARS,
            self::GENERIC_DEATH,
            [
                'Наступив на кочку, вы внезапно улетели в небеса. ☁',
                'Вы увидели 🌲 <b>лес</b> как на ладони и узнали, где выход.',
                'Упав с большой высоты, вы разбились. 💥',
                'Последней вашей мыслью было <i>«Неужели мишки Гамми...»</i> 🤔',
            ]
        );
    }
}
