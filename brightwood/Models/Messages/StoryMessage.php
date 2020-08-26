<?php

namespace Brightwood\Models\Messages;

class StoryMessage extends Message
{
    private int $nodeId;

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
        parent::__construct($lines, $actions);

        $this->nodeId = $nodeId;
    }

    public function nodeId() : int
    {
        return $this->nodeId;
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
