<?php

namespace App\Chunks\Core;

use App\Chunks\Core\Interfaces\ChunkConfigInterface;
use App\Chunks\Core\Interfaces\ChunkInterface;
use Psr\Container\ContainerInterface;

class ChunkSource
{
    private ContainerInterface $container;
    private ChunkConfigInterface $chunkConfig;

    public function __construct(
        ContainerInterface $container,
        ChunkConfigInterface $chunkConfig
    )
    {
        $this->container = $container;
        $this->chunkConfig = $chunkConfig;
    }

    public function get(string $name): ?ChunkInterface
    {
        if (!$this->chunkConfig->has($name)) {
            return null;
        }

        $chunkClass = $this->chunkConfig->get($name);

        return $this->container->get($chunkClass);
    }
}
