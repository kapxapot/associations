<?php

namespace App\Controllers;

use App\Config\Interfaces\NewsConfigInterface;
use App\Services\GameService;
use Plasticode\Services\NewsAggregatorService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends Controller
{
    private GameService $gameService;
    private NewsAggregatorService $newsAggregatorService;
    private NewsConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameService = $container->get(GameService::class);
        $this->newsAggregatorService = $container->get(NewsAggregatorService::class);
        $this->config = $container->get(NewsConfigInterface::class);
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $debug = $this->isDebug($request);

        $user = $this->auth->getUser();

        $game = $user
            ? $this->gameService->getOrCreateNewGameFor($user)
            : null;

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
                    'news' => $latestNews,
                    'debug' => $debug,
                ],
            ]
        );

        return $this->render($response, 'main/index.twig', $params);
    }
}
