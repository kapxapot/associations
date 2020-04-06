<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Auth\Access;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class GameController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;
    private NotFoundHandler $notFoundHandler;
    private AuthInterface $auth;
    private Access $access;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->gameRepository;
        $this->languageRepository = $container->languageRepository;
        $this->notFoundHandler = $container->notFoundHandler;
        $this->auth = $container->auth;
        $this->access = $container->access;
    }

    public function get(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $game = $this->gameRepository->get($id);
        $user = $this->auth->getUser();

        if (is_null($game) || is_null($user)) {
            return ($this->notFoundHandler)($request, $response);
        }

        $canSeeAllGames = $this->access->checkRights('games', 'edit', $user);
        $hasPlayer = $game->hasPlayer($user);

        if (!$canSeeAllGames && !$hasPlayer) {
            return ($this->notFoundHandler)($request, $response);
        }

        $params = $this->buildParams(
            [
                'params' => [
                    'title' => $game->displayName(),
                    'game' => $game,
                    'disqus_id' => 'game' . $game->getId(),
                    'debug' => $debug,
                ],
            ]
        );
        
        return $this->render($response, 'main/games/item.twig', $params);
    }
    
    public function start(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $languageId = $request->getParam('language_id');
        $language = $this->languageRepository->get($languageId);

        if ($language === null) {
            throw new NotFoundException('Language not found.');
        }

        if ($user->currentGame() !== null) {
            throw new BadRequestException('Game is already on.');
        };

        $this->gameService->newGame($language, $user);
        
        return Response::json(
            $response,
            ['message' => $this->translate('New game started.')]
        );
    }

    public function finish(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->getUser();
        
        if ($user) {
            $game = $user->currentGame();
            
            if ($game !== null) {
                $this->gameService->finishGame($game);
            }
        }
        
        return Response::json(
            $response,
            ['message' => $this->translate('Game finished.')]
        );
    }
}
