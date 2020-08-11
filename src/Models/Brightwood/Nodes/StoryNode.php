<?php

namespace App\Models\Brightwood\Nodes;

use App\Models\Brightwood\Stories\Story;
use App\Models\Brightwood\StoryMessage;

abstract class StoryNode
{
    protected ?Story $story = null;
    protected int $id;
    protected string $text;

    public function __construct(
        int $id,
        string $text
    )
    {
        $this->id = $id;
        $this->text = $text;
    }

    public function id() : int
    {
        return $this->id;
    }

    public function text() : string
    {
        return $this->text;
    }

    /**
     * @return static
     */
    public function withStory(Story $story) : self
    {
        $this->story = $story;
        return $this;
    }

    abstract public function isFinish() : bool;

    public function getMessage() : StoryMessage
    {
        return new StoryMessage(
            $this->id,
            [$this->text]
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
    }

    protected function resolveNode(int $id) : self
    {
        return $this->story->getNode($id);
    }
}
