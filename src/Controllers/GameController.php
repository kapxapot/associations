<?php

namespace App\Controllers;

use App\Models\Game;

class GameController extends Controller
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];
		
	    $debug = $request->getQueryParam('debug', null) !== null;

		$game = Game::get($id);

		if ($game === null) {
			return $this->notFound($request, $response);
		}

	    $params = $this->buildParams([
	        'params' => [
    	        'title' => $game->displayName(),
    	        'game' => $game,
    			'disqus_id' => 'game' . $game->getId(),
    	        'debug' => $debug,
            ],
        ]);
	    
		return $this->view->render($response, 'main/games/item.twig', $params);
	}
    
	public function finish($request, $response, $args)
	{
	    $user = $this->auth->getUser();
	    
	    if ($user) {
	        $game = $user->currentGame();
	        
	        if ($game !== null) {
	            $this->gameService->finish($game);
	        }
	    }

		return $response;
	}
}
