<?php

namespace App\Repositories\Interfaces;

use App\Collections\TurnCollection;
use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;

interface TurnRepositoryInterface
{
    function get(?int $id): ?Turn;
    function getAllByGame(Game $game): TurnCollection;
    function getAllByAssociation(Association $association): TurnCollection;
    function getAllByLanguage(Language $language): TurnCollection;
    function getAllByUser(User $user, Language $language = null): TurnCollection;
    function getAllByWord(Word $word): TurnCollection;
}
