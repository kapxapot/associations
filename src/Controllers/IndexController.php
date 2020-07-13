<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Services\GameService;
use Plasticode\Services\NewsAggregatorService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class IndexController extends Controller
{
    private AuthInterface $auth;

    private GameService $gameService;
    private NewsAggregatorService $newsAggregatorService;
    private NewsConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->auth = $container->auth;

        $this->gameService = $container->gameService;
        $this->newsAggregatorService = $container->newsAggregatorService;
        $this->config = $container->config;
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

        // if there is no current game, start it
        if ($user && is_null($game)) {
            $language = $lastGame
                ? $lastGame->language()
                : $this->languageService->getDefaultLanguage();

            $game = $this->gameService->newGame($language, $user);
        }

        $latestNews = $this
            ->newsAggregatorService
            ->getLatest(
                $this->config->newsLatestLimit(),
                0,
                false
            );

        $params = $this->buildParams(
            [
                'params' => [
                    'game' => $game,
                    'last_game' => $lastGame,
                    'last_game_turn_count_str' => $turnCountStr,
                    'news' => $latestNews,
                    'debug' => $debug,
                ],
            ]
        );

        return $this->render($response, 'main/index.twig', $params);
    }
}
