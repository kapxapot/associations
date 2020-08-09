<?php

namespace App\Repositories\Brightwood;

use App\Collections\Brightwood\StoryCollection;
use App\Models\Brightwood\Links\ActionLink;
use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
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
                'Вы гуляли по лесу и заблудились.',
                new ActionLink(2, 'Сесть на пенек и заплакать'),
                new ActionLink(1, 'Попытаться найти выход')
            )
        );

        $story->addNode(
            new FinishNode(
                2,
                'Вы умерли от голода.'
            )
        );

        $story->checkIntegrity();

        return $story;
    }
}
