<?php

namespace Brightwood\Models\Messages\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MessageInterface extends SequencableInterface
{
    /**
     * @return string[]
     */
    public function lines(): array;

    /**
     * @return $this
     */
    public function prependLines(?string ...$lines): self;

    /**
     * @return $this
     */
    public function appendLines(?string ...$lines): self;

    /**
     * @return $this
     */
    public function withLines(?string ...$lines): self;

    /**
     * @return (string|string[])[]
     */
    public function actions(): array;

    public function hasActions(): bool;

    /**
     * @param (string|string[]|null)[] $actions
     *
     * @return $this
     */
    public function appendActions(...$actions): self;

    public function data(): ?StoryData;

    public function hasData(): bool;

    public function image(): ?string;

    /**
     * @return $this
     */
    public function withImage(string $image): self;
}
