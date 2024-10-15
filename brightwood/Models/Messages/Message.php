<?php

namespace Brightwood\Models\Messages;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Util\Util;

class Message implements MessageInterface
{
    /** @var string[] */
    protected array $lines;

    /** @var (string|string[])[] */
    protected array $actions;

    protected ?string $image;

    /**
     * @param (string|null)[]|null $lines
     * @param (string|null)[]|null $actions
     */
    public function __construct(
        ?array $lines = null,
        ?array $actions = null,
        ?string $image = null
    )
    {
        $this->lines = Util::clean($lines);
        $this->actions = Util::clean($actions);
        $this->image = $image;
    }

    /**
     * @return string[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return (string|string[])[]
     */
    public function actions(): array
    {
        return $this->actions;
    }

    public function image(): ?string
    {
        return $this->image;
    }

    public function hasActions(): bool
    {
        return !empty($this->actions);
    }

    public function data(): ?StoryData
    {
        return null;
    }

    public function hasData(): bool
    {
        return $this->data() !== null;
    }

    public function prependLines(?string ...$lines): self
    {
        $this->lines = array_merge(
            Util::clean($lines),
            $this->lines
        );

        return $this;
    }

    public function appendLines(?string ...$lines): self
    {
        $this->lines = array_merge(
            $this->lines,
            Util::clean($lines)
        );

        return $this;
    }

    public function withLines(?string ...$lines): self
    {
        $this->lines = Util::clean($lines);
        return $this;
    }

    public function appendActions(...$actions): self
    {
        $this->actions = array_merge(
            $this->actions,
            Util::clean($actions)
        );

        return $this;
    }

    public function withImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
