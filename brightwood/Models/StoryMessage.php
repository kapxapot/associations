<?php

namespace Brightwood\Models;

class StoryMessage
{
    private int $nodeId;

    /** @var string[] */
    private array $lines;

    /** @var string[] */
    private array $actions;

    /**
     * @param string[] $lines
     * @param string[]|null $actions
     */
    public function __construct(
        int $nodeId,
        ?array $lines = null,
        ?array $actions = null
    )
    {
        $this->nodeId = $nodeId;
        $this->lines = $lines ?? [];
        $this->actions = $actions ?? [];
    }

    public function nodeId() : int
    {
        return $this->nodeId;
    }

    /**
     * @return string[]
     */
    public function lines() : array
    {
        return $this->lines;
    }

    /**
     * @return string[]
     */
    public function actions() : array
    {
        return $this->actions;
    }

    /**
     * @return static
     */
    public function withLines(string ...$lines) : self
    {
        return $this->merge(
            new static($this->nodeId, $lines)
        );
    }

    /**
     * @return static
     */
    public function prependLines(string ...$lines) : self
    {
        return (new static(0, $lines))->merge($this);
    }

    /**
     * @return static
     */
    public function withActions(string ...$actions) : self
    {
        return $this->merge(
            new static($this->nodeId, null, $actions)
        );
    }

    /**
     * @param static ...$messages
     * @return static
     */
    public function merge(self ...$messages) : self
    {
        $nodeId = $this->nodeId;

        /** @var string[] */
        $lines = $this->lines;

        /** @var string[] */
        $actions = $this->actions;

        /** @var static */
        foreach ($messages as $message) {
            $nodeId = $message->nodeId();
            $lines = array_merge($lines, $message->lines());
            $actions = array_merge($actions, $message->actions());
        }

        return new static($nodeId, $lines, $actions);
    }
}
