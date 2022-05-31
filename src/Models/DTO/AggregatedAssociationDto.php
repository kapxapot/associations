<?php

namespace App\Models\DTO;

class AggregatedAssociationDto
{
    private int $associationId;
    private int $anchorId;
    private bool $junky;
    private ?string $log;

    public function __construct(
        int $associationId,
        int $anchorId,
        bool $junky = false,
        string $log = null
    )
    {
        $this->associationId = $associationId;
        $this->anchorId = $anchorId;
        $this->junky = $junky;
        $this->log = $log;
    }

    public function associationId(): int
    {
        return $this->associationId;
    }

    public function anchorId(): int
    {
        return $this->anchorId;
    }

    public function junky(): bool
    {
        return $this->junky;
    }

    public function log(): ?string
    {
        return $this->log;
    }

    public static function fromArray(array $array): self
    {
        [$associationId, $anchorId, $junky, $log] = $array;

        return new self($associationId, $anchorId, $junky, $log);
    }
}
