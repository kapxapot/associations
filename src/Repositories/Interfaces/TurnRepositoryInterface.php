<?php

namespace App\Repositories\Interfaces;

use App\Collections\TurnCollection;
use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface TurnRepositoryInterface extends GetRepositoryInterface, WithLanguageRepositoryInterface
{
    public function get(?int $id): ?Turn;

    public function save(Turn $turn): Turn;

    public function getAllByGame(Game $game): TurnCollection;

    public function getAllByAssociation(Association $association): TurnCollection;

    public function getAllByLanguage(Language $language): TurnCollection;

    public function getAllByUser(User $user, ?Language $language = null): TurnCollection;

    public function getAllByWord(Word $word): TurnCollection;
}
