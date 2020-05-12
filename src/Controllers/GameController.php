<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\GameService;
use App\Services\TurnService;
use Plasticode\Auth\Access;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;
use Webmozart\Assert\Assert;

class GameController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;

    private Access $access;
    private AuthInterface $auth;
    private GameService $gameService;
    private NotFoundHandler $notFoundHandler;
    private TurnService $turnService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->gameRepository;
        $this->languageRepository = $container->languageRepository;

        $this->access = $container->access;
        $this->auth = $container->auth;
        $this->gameService = $container->gameService;
        $this->notFoundHandler = $container->notFoundHandler;
        $this->turnService = $container->turnService;
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

        $canSeeAllGames = $this->access->checkActionRights('games', 'edit', $user);
        $isPlayer = $game->hasPlayer($user);

        if (!$canSeeAllGames && !$isPlayer) {
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

        if (is_null($language)) {
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

    public function finish(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        Assert::notNull($user);
        
        $game = $user->currentGame();

        /** @var string */
        $msg = null;
        
        if ($game !== null) {
            $this->turnService->finishGame($game);
            $msg = 'Game finished.';
        } else {
            $msg = 'No current game found.';
        }
        
        return Response::json(
            $response,
            ['message' => $this->translate($msg)]
        );
    }
}
