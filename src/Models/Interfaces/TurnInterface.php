<?php

namespace App\Models\Interfaces;

use App\Models\Association;
use App\Models\Word;

interface TurnInterface
{
    public function association(): ?Association;

    public function word(): ?Word;
}
