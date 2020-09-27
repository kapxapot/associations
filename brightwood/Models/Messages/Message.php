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

    public function hasActions() : bool
    {
        return !empty($this->actions);
    }

    public function data() : ?StoryData
    {
        return null;
    }

    public function hasData() : bool
    {
        return $this->data !== null;
    }

    /**
     * @return static
     */
    public function prependLines(string ...$lines) : self
    {
        $this->lines = array_merge(
            $lines,
            $this->lines
        );

        return $this;
    }

    /**
     * @return static
     */
    public function appendLines(string ...$lines) : self
    {
        $this->lines = array_merge(
            $this->lines,
            $lines
        );

        return $this;
    }

    /**
     * @return static
     */
    public function appendActions(string ...$actions) : self
    {
        $this->actions = array_merge(
            $this->actions,
            $actions
        );

        return $this;
    }
}
