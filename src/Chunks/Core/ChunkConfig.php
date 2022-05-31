<?php

namespace App\Chunks\Core;

use App\Chunks\Core\Interfaces\ChunkConfigInterface;

class ChunkConfig implements ChunkConfigInterface
{
    /** @var array<string, string> */
    private array $chunks = [];

    public function __construct(array $chunks = [])
    {
        foreach ($chunks as $name => $class) {
            $this->register($name, $class);
        }
    }

    /**
     * @return $this
     */
    public function register(string $name, string $class): self
    {
        $this->chunks[$name] = $class;

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->chunks);
    }

    public function get(string $name): ?string
    {
        return $this->chunks[$name] ?? null;
    }
}
