<?php

namespace App\Controllers;

class IndexController extends Controller
{
    public function index($request, $response, $args)
    {
        $debug = $request->getQueryParam('debug', null) !== null;
        
        $user = $this->auth->getUser();

        $game = $user ? $user->currentGame() : null;
        $lastTurn = $game ? $game->lastTurn() : null;
        $word = $lastTurn ? $lastTurn->word() : null;
        $association = $lastTurn ? $lastTurn->association() : null;

        /*if ($debug) {
            die('ok');
        }*/
        
        $params = $this->buildParams([
            'params' => [
                'game' => $game,
                'last_game' => $user ? $user->lastGame() : null,
                'debug' => $debug,
            ],
        ]);
        
        return $this->view->render($response, 'main/index.twig', $params);
    }
}
