<?php

namespace App\Core\Interfaces;

use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use Plasticode\Core\Interfaces\LinkerInterface as PlasticodeLinkerInterface;

interface LinkerInterface extends PlasticodeLinkerInterface
{
    function association(Association $association) : ?string;
    function game(Game $game) : ?string;
    function word(Word $word) : ?string;
}
