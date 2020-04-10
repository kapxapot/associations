<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class IndexController extends Controller
{
    private AuthInterface $auth;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->auth = $container->auth;
    }

    public function index(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $debug = $request->getQueryParam('debug', null) !== null;

        $user = $this->auth->getUser();

        $game = $user
            ? $user->currentGame()
            : null;

        $lastGame = $user
            ? $user->lastGame()
            : null;

        $turnCountStr = $lastGame
            ? $this->casesService->turnCount(
                $lastGame->turns()->count()
            )
            : null;

        $params = $this->buildParams(
            [
                'params' => [
                    'game' => $game,
                    'last_game' => $lastGame,
                    'last_game_turn_count_str' => $turnCountStr,
                    'debug' => $debug,
                ],
            ]
        );

        return $this->render($response, 'main/index.twig', $params);
    }
}
