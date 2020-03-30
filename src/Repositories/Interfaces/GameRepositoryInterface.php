<?php

namespace App\Repositories\Interfaces;

use App\Models\Game;

interface GameRepositoryInterface
{
    function get(?int $id): ?Game;
}
