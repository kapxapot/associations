<?php

namespace Brightwood\Testing\Models;

use Brightwood\Models\Stories\Core\Story;
use Brightwood\StoryBuilder;

class TestStory extends Story
{
    const ID = 1;
    const TITLE = 'Лес';
    const DESCRIPTION = 'Blah';

    public function __construct()
    {
        parent::__construct(['id' => self::ID]);

        $this->title = self::TITLE;
        $this->description = self::DESCRIPTION;

        $this->prepare();
    }

    public function newData(): TestData
    {
        return new TestData();
    }

    public function loadData(array $data): TestData
    {
        return new TestData($data);
    }

    protected function build(): void
    {
        $builder = new StoryBuilder($this);

        $this->setPrefixMessage('День: {day}');

        $start = $builder->addActionNode(
            1,
            'Вы гуляли по лесу 🌲🌲🌲 и заблудились. 😮',
            [
                [5, 'Сесть на пенек и заплакать'],
                [3, 'Попытаться найти выход'],
            ]
        );

        $this->setStartNode($start);

        $builder->addFinishNode(2, 'Вы умерли от <b>голода</b>. 💀');

        $builder->addRedirectNode(
            3,
            'Вы долго бродили по лесу 🌲🌲🌲 в поисках выхода.',
            [[1, 4], 4]
        );

        $builder->addFinishNode(
            4,
            'Вы нашли дорогу и выбрались из леса. 🎉🎉🎉'
        );

        $builder
            ->addRedirectNode(
                5,
                'Вы сели на пенек, проплакали весь день и уснули. 😴',
                [[6, 3], 7, 2]
            )
            ->does(
                fn (TestData $d) => $d->nextDay()
            );

        $builder->addActionNode(
            6,
            'Проснувшись, вы обнаружили, что вы все еще не знаете, где выход из леса. 😕',
            [
                [5, 'Сесть на пенек и заплакать'],
                [3, 'Попытаться найти выход'],
            ]
        );

        $builder->addSkipNode(
            7,
            1,
            'Вас разбудила <b>избирательная комиссия</b> 👩‍👩‍👧‍👧, которой понадобился ваш пенек. 🤔 Вам пришлось уйти.'
        );

        // yes, this is not linked to anything
        // just for a test
        $builder->addFinishNode(8);
    }
}
