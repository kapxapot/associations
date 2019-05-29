<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\Exceptions\ApplicationException;

use App\Models\Game;

class GameTurnIsCorrect extends AbstractRule
{
    private $turnId;
    
    public function __construct($turnId)
    {
        $this->turnId = $turnId;
    }
    
	public function validate($input)
	{
	    $game = Game::get($input);
	    
	    if ($game === null) {
	        throw new ApplicationException('Game not found.');
	    }
	    
	    $lastTurnId = $game->lastTurn()
	        ? $game->lastTurn()->getId()
	        : null;

		return $this->turnId === $lastTurnId;
	}
}
