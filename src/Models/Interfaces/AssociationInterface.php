<?php

namespace App\Models\Interfaces;

use App\Models\Association;
use App\Models\Word;

interface AssociationInterface
{
    public function getFirstWord(): Word;

    public function getSecondWord(): Word;

    public function isReal(): bool;

    public function toReal(): ?Association;

    /**
     * Returns unique association key in format '[first word id]:[second word id]'.
     */
    public function key(): string;
}
