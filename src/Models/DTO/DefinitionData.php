<?php

namespace App\Models\DTO;

class DefinitionData
{
    private string $source;
    private string $url;
    private ?string $jsonData;

    public function __construct(string $source, string $url, ?string $jsonData)
    {
        $this->source = $source;
        $this->url = $url;
        $this->jsonData = $jsonData;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function jsonData(): ?string
    {
        return $this->jsonData;
    }

    public function isEmpty(): bool
    {
        return $this->jsonData === null;
    }
}
