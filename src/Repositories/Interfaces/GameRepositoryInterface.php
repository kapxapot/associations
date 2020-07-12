<?php

namespace App\Repositories\Interfaces;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;

interface GameRepositoryInterface extends WithLanguageRepositoryInterface
{
    function get(?int $id) : ?Game;
    function getAllByLanguage(Language $language) : GameCollection;
    function save(Game $game) : Game;
    function store(array $data) : Game;
    function getCurrentByUser(User $user) : ?Game;
    function getLastByUser(User $user) : ?Game;
}
