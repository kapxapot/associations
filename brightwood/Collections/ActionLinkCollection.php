<?php

namespace Brightwood\Collections;

use Brightwood\Models\Links\ActionLink;

class ActionLinkCollection extends StoryLinkCollection
{
    protected string $class = ActionLink::class;

    /**
     * @return string[]
     */
    public function actions(): array
    {
        return $this
            ->map(
                fn (ActionLink $l) => $l->action()
            )
            ->toArray();
    }
}
