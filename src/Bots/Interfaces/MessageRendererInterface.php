<?php

namespace App\Bots\Interfaces;

interface MessageRendererInterface
{
    /**
     * @return $this
     */
    public function withGender(int $gender): self;

    /**
     * @param array<string, mixed> $vars
     * @return $this
     */
    public function withVars(array $vars): self;

    public function render(string $text): string;
}
