<?php

namespace App\Repositories\Interfaces;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface GameRepositoryInterface extends FilteringRepositoryInterface, GetRepositoryInterface, WithLanguageRepositoryInterface
{
    public function get(?int $id): ?Game;

    public function getAllByLanguage(Language $language): GameCollection;

    public function store(array $data): Game;

    public function save(Game $game): Game;

    public function getCurrentByUser(User $user): ?Game;

    public function getLastByUser(User $user): ?Game;
}
