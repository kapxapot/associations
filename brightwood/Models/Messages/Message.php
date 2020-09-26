<?php

namespace Brightwood\Models\Messages;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;

class Message implements MessageInterface
{
    /** @var string[] */
    protected array $lines;

    /** @var string[] */
    protected array $actions;

    /**
     * @param string[] $lines
     * @param string[]|null $actions
     */
    public function __construct(
        ?array $lines = null,
        ?array $actions = null
    )
    {
        $this->lines = $lines ?? [];
        $this->actions = $actions ?? [];
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

    public function data() : ?StoryData
    {
        return null;
    }

    public function appendLines(string ...$lines) : self
    {
        $allLines = array_merge(
            $this->lines,
            $lines
        );

        return new self($allLines, $this->actions);
    }

    public function prependLines(string ...$lines) : self
    {
        $allLines = array_merge(
            $lines,
            $this->lines
        );

        return new self($allLines, $this->actions);
    }
}
