<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class IndexController extends Controller
{
    public function index(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $debug = $request->getQueryParam('debug', null) !== null;
        
        $user = $this->auth->getUser();
        $game = $user ? $user->currentGame() : null;
        
        $params = $this->buildParams(
            [
                'params' => [
                    'game' => $game,
                    'last_game' => $user ? $user->lastGame() : null,
                    'debug' => $debug,
                ],
            ]
        );
        
        return $this->render($response, 'main/index.twig', $params);
    }
}
