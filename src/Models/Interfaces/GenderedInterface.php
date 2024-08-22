<?php

namespace App\Models\Interfaces;

interface GenderedInterface
{
    public function hasGender(): bool;

    /**
     * Returns gender if it's defined.
     */
    public function gender(): ?int;
}
