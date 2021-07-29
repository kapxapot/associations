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

    /**
     * @param array<string, callable> $handlers
     * @return $this
     */
    public function withHandlers(array $handlers): self;

    public function render(string $text): string;
}
