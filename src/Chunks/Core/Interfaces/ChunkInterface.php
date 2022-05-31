<?php

namespace App\Chunks\Core\Interfaces;

use App\Chunks\Core\ChunkResult;

interface ChunkInterface
{
    public function process(array $params): ChunkResult;
}
