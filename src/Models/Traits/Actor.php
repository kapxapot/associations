<?php

namespace App\Models\Traits;

/**
 * Implements {@see App\Models\Interfaces\ActorInterface}.
 */
trait Actor
{
    use Gendered;

    public function hasLanguageCode(): bool
    {
        return $this->languageCode() !== null;
    }

    abstract public function languageCode(): ?int;
}
