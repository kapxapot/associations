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
     * @param mixed $value
     * @return $this
     */
    public function withVar(string $name, $value): self;

    /**
     * @param array<string, callable> $handlers
     * @return $this
     */
    public function withHandlers(array $handlers): self;

    /**
     * @param mixed $handler
     * @return $this
     */
    public function withHandler(string $name, callable $handler): self;

    public function render(string $text): string;
}
