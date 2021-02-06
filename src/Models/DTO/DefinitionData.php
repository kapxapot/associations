<?php

namespace App\Models\DTO;

class DefinitionData
{
    private string $source;
    private string $url;
    private ?string $data;

    public function __construct(string $source, string $url, ?string $data)
    {
        $this->source = $source;
        $this->url = $url;
        $this->data = $data;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function data(): ?string
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return $this->data === null;
    }
}
