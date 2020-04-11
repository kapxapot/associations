<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Services\GameService;
use App\Services\TurnService;
use App\Services\WordService;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class TurnController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;

    private AuthInterface $auth;
    private GameService $gameService;
    private TurnService $turnService;
    private WordService $wordService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->gameRepository;
        $this->turnRepository = $container->turnRepository;

        $this->auth = $container->auth;
        $this->gameService = $container->gameService;
        $this->turnService = $container->turnService;
        $this->wordService = $container->wordService;
    }

    public function create(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        // validate game
        $gameId = $request->getParam('game_id');
        $game = $this->gameRepository->get($gameId);

        if (is_null($game)) {
            throw new NotFoundException('Game not found.');
        }

        if ($game->getId() !== $user->currentGame()->getId()) {
            throw new BadRequestException(
                'Game is finished. Please, reload the page.'
            );
        }

        $language = $game->language();

        // validate prev turn
        $prevTurnId = $request->getParam('prev_turn_id');
        $prevTurn = $this->turnRepository->get($prevTurnId);

        if (!$this->gameService->validateLastTurn($game, $prevTurn)) {
            throw new BadRequestException(
                'Game turn is not correct. Please, reload the page.'
            );
        }

        // validate word
        $wordStr = $request->getParam('word');
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        $this->wordService->validateWord($wordStr);

        if (!$this->turnService->validatePlayerTurn($game, $wordStr)) {
            throw new BadRequestException(
                'Word is already used in this game.'
            );
        }

        // get word
        $word = $this->wordService->getOrCreate($language, $wordStr, $user);

        // new turn
        $this->turnService->newPlayerTurn($game, $word, $user);
        
        return Response::json(
            $response,
            ['message' => $this->translate('Turn successfully done.')]
        );
    }
}
