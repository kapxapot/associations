<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;

use App\Models\Game;

class GamesGenerator extends EntityGenerator
{
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['language_id'] = $this
		    ->rule('posInt')
		    ->languageExists()
		    ->noCurrentGame();

		return $rules;
	}
	
	public function beforeSave($data, $id = null)
	{
	    $data = parent::beforeSave($data, $id);

        $data['user_id'] = $this->auth->getUser()->getId();

		return $data;
	}
	
	public function afterSave($item, $data)
	{
	    parent::afterSave($item, $data);
	    
		$game = Game::get($item->id);
		
		if ($game !== null) {
		    $this->gameService->start($game);
		}
	}
}
