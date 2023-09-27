<?php

namespace Brightwood\Models\Links;

class ActionLink extends StoryLink
{
    private string $action;

    public function __construct(int $nodeId, string $action)
    {
        parent::__construct($nodeId);

        $this->action = $action;
    }

    public function action(): string
    {
        return $this->action;
    }
}
