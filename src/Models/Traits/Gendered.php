<?php

namespace App\Models\Traits;

/**
 * Implements {@see App\Models\Interfaces\GenderedInterface}.
 */
trait Gendered
{
    public function hasGender() : bool
    {
        return $this->gender() !== null;
    }

    abstract public function gender() : ?int;
}
