<?php

namespace App\Models\Interfaces;

use App\Models\Word;

interface AssociationInterface
{
    public function getFirstWord(): Word;

    public function getSecondWord(): Word;

    public function isReal(): bool;
}
