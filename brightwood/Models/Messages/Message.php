<?php

namespace Brightwood\Models\Messages;

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

    public function prependLines(string ...$lines) : self
    {
        $allLines = array_merge(
            $lines,
            $this->lines
        );

        return new static($allLines, $this->actions);
    }
}
