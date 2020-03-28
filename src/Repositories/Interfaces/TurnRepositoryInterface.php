<?php

namespace App\Repositories\Interfaces;

use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use Plasticode\Collection;

interface TurnRepositoryInterface
{
    function get(?int $id) : ?Turn;
    function getAllByGame(Game $game) : Collection;
    function getAllByAssociation(Association $association) : Collection;
    function getAllByLanguage(Language $language) : Collection;
    function getAllByUser(User $user, Language $language = null) : Collection;
    function getAllByWord(Word $word) : Collection;
}
