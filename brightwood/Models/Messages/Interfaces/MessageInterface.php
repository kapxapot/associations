<?php

namespace Brightwood\Models\Messages\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MessageInterface extends SequencableInterface
{
    /**
     * @return string[]
     */
    public function lines(): array;

    public function prependLines(string ...$lines): self;

    public function appendLines(string ...$lines): self;

    /**
     * @return string[]
     */
    public function actions(): array;

    public function hasActions(): bool;

    public function appendActions(string ...$actions): self;

    public function data(): ?StoryData;

    public function hasData(): bool;
}
