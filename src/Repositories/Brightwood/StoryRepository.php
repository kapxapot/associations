<?php

namespace App\Repositories\Brightwood;

use App\Collections\Brightwood\StoryCollection;
use App\Models\Brightwood\Links\ActionLink;
use App\Models\Brightwood\Links\RedirectLink;
use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
use App\Models\Brightwood\Nodes\RedirectNode;
use App\Models\Brightwood\Nodes\SkipNode;
use App\Models\Brightwood\Nodes\StartNode;
use App\Models\Brightwood\Story;
use App\Repositories\Brightwood\Interfaces\StoryRepositoryInterface;

/**
 * Stub repository for now.
 */
class StoryRepository implements StoryRepositoryInterface
{
    private StoryCollection $stories;

    public function __construct()
    {
        $this->stories = StoryCollection::make(
            [
                $this->buildDemoStory(1)
            ]
        );
    }

    public function get(?int $id) : ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->id() == $id
        );
    }

    private function buildDemoStory(int $id) : Story
    {
        $story = new Story($id);

        $story->setStartNode(
            new ActionNode(
                1,
                'Вы гуляли по лесу 🌲🌲🌲 и заблудились. 😮',
                new ActionLink(5, 'Сесть на пенек и заплакать'),
                new ActionLink(3, 'Попытаться найти выход')
            )
        );

        $story->addNode(
            new FinishNode(2, 'Вы умерли от <b>голода</b>. 💀')
        );

        $story->addNode(
            new RedirectNode(
                3,
                'Вы долго бродили по лесу 🌲🌲🌲 в поисках выхода.',
                [
                    1 => 4,
                    4 => 1
                ]
            )
        );

        $story->addNode(
            new FinishNode(4, 'Вы нашли дорогу и выбрались из леса. 🎉🎉🎉')
        );

        $story->addNode(
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

        $story->addNode(
            new ActionNode(
                6,
                'Проснувшись, вы обнаружили, что вы все еще не знаете, где выход из леса. 😕',
                new ActionLink(5, 'Сесть на пенек и заплакать'),
                new ActionLink(3, 'Попытаться найти выход')
            )
        );

        $story->addNode(
            new SkipNode(
                7,
                'Вас разбудила <b>избирательная комиссия</b> 👩‍👩‍👧‍👧, которой понадобился ваш пенек. 🤔 Вам пришлось уйти.',
                1
            )
        );

        $story->checkIntegrity();

        return $story;
    }
}
