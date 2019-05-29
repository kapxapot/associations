<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\ContainerRule;

class NoCurrentGame extends ContainerRule
{
	public function validate($input)
	{
	    parent::validate($input);

	    $user = $this->container->auth->getUser();
	    
		return $user !== null && $user->currentGame() === null;
	}
}
