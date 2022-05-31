<?php

namespace App\Chunks\Core\Factories;

use App\Chunks\Core\ChunkConfig;
use App\Chunks\Core\Interfaces\ChunkConfigInterface;
use App\Chunks\WordOriginChunk;

class ChunkConfigFactory
{
    public function __invoke(): ChunkConfigInterface
    {
        return new ChunkConfig([
            'word-origin' => WordOriginChunk::class,
        ]);
    }
}
