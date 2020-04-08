<?php

namespace App\Repositories\Interfaces;

use App\Models\Game;
use App\Models\User;

interface GameRepositoryInterface
{
    function get(?int $id) : ?Game;
    function store(array $data) : Game;
    function getCurrentByUser(User $user) : ?Game;
    function getLastByUser(User $user) : ?Game;
}
