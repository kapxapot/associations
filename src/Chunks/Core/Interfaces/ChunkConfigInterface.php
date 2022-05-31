<?php

namespace App\Chunks\Core\Interfaces;

interface ChunkConfigInterface
{
    /**
     * @return $this
     */
    public function register(string $name, string $class): self;

    public function has(string $name): bool;

    public function get(string $name): ?string;
}
