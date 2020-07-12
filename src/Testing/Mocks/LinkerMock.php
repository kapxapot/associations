<?php

namespace App\Testing\Mocks;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use Plasticode\Testing\Mocks\LinkerMock as BaseLinkerMock;

class LinkerMock extends BaseLinkerMock implements LinkerInterface
{
    public function association(Association $association) : ?string
    {
        return $this->abs('/associations/') . $association->getId();
    }

    public function game(Game $game) : ?string
    {
        return $this->abs('/games/') . $game->getId();
    }

    public function word(Word $word) : ?string
    {
        return $this->abs('/words/') . $word->getId();
    }
}
