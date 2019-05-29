<?php

namespace App\Validation\Rules;

use Plasticode\Exceptions\ApplicationException;
use Plasticode\Validation\Rules\ContainerRule;

class GameIsCurrent extends ContainerRule
{
	public function validate($input)
	{
	    parent::validate($input);
	    
	    $user = $this->container->auth->getUser();
	    
	    if ($user === null) {
	        throw new ApplicationException('No current user.');
	    }
	    
		$game = $user->currentGame();

		return $game !== null && $game->getId() === intval($input);
	}
}
