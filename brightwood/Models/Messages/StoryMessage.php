<?php

namespace Brightwood\Models\Messages;

use Brightwood\Models\Data\StoryData;

class StoryMessage extends Message
{
    private int $nodeId;

    protected ?StoryData $data = null;

    /**
     * @param string[]|null $lines
     * @param string[]|null $actions
     */
    public function __construct(
        int $nodeId,
        ?array $lines = null,
        ?array $actions = null,
        ?StoryData $data = null
    )
    {
        parent::__construct($lines, $actions);

        $this->nodeId = $nodeId;
        $this->data = $data;
    }

    public function nodeId(): int
    {
        return $this->nodeId;
    }

    public function data(): ?StoryData
    {
        return $this->data;
    }

    public function withData(StoryData $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function merge(self ...$messages): self
    {
        $nodeId = $this->nodeId;
        $lines = $this->lines;
        $actions = $this->actions;
        $data = $this->data;

        foreach ($messages as $message) {
            if ($message->nodeId() > 0) {
                $nodeId = $message->nodeId();
            }

            $lines = array_merge($lines, $message->lines());
            $actions = array_merge($actions, $message->actions());

            if ($message->data()) {
                $data = $message->data();
            }
        }

        return new self($nodeId, $lines, $actions, $data);
    }
}
