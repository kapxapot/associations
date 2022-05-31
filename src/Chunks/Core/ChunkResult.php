<?php

namespace App\Chunks\Core;

class ChunkResult
{
    public string $template;
    public ?array $data = null;

    public function __construct(string $template, ?array $data = null)
    {
        $this->template = $template;
        $this->data = $data;
    }
}
