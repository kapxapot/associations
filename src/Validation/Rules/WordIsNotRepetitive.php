<?php

namespace App\Validation\Rules;

use Plasticode\Exceptions\ApplicationException;
use Plasticode\Validation\Rules\ContainerRule;

use App\Models\Game;

class WordIsNotRepetitive extends ContainerRule
{
    private $gameId;
    
    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }
    
	public function validate($input)
	{
	    parent::validate($input);
	    
	    $game = Game::get($this->gameId);
	    
	    if ($game === null) {
	        throw new ApplicationException('Game not found.');
	    }
	    
	    return $this->container->turnService->validatePlayerTurn($game, $input);
	}
}
