<?php

namespace App\Core\Interfaces;

use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Core\Interfaces\LinkerInterface as PlasticodeLinkerInterface;

interface LinkerInterface extends PlasticodeLinkerInterface
{
    public function association(Association $association): ?string;

    public function game(Game $game): ?string;

    public function word(Word $word): ?string;

    public function story(Story $story): ?string;
}
