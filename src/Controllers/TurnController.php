<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Models\Turn;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Services\GameService;
use App\Services\TurnService;
use App\Services\WordService;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Exceptions\InvalidResultException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;
use Webmozart\Assert\Assert;

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

        $currentGame = $user->currentGame();

        if (!$game->equals($currentGame)) {
            throw new BadRequestException(
                'Game is finished. Please, reload the page.'
            );
        }

        // validate prev turn
        $prevTurnId = $request->getParam('prev_turn_id');
        $prevTurn = $this->turnRepository->get($prevTurnId);

        if ($prevTurn && !$this->gameService->validateLastTurn($game, $prevTurn)) {
            throw new BadRequestException(
                'Game turn is not correct. Please, reload the page.'
            );
        }

        // validate word
        $wordStr = $request->getParam('word');

        try {
            $turns = $this->gameService->makeTurn($user, $game, $wordStr);
        } catch (InvalidResultException $ex) {
            throw new BadRequestException($ex->getMessage());
        }

        /** @var Turn */
        $question = $turns->first();

        /** @var Turn|null */
        $answer = $turns->skip(1)->first();

        $result = [
            'question' => $this->serializer->serializeTurn($question),
            'answer' => $this->serializer->serializeTurn($answer)
        ];

        if (is_null($answer)) {
            $newGame = $this->gameService->createGameFor($user);

            $result['new'] = $this->serializer->serializeTurn(
                $newGame->lastTurn()
            );
        }

        return Response::json($response, $result);
    }

    public function skip(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        Assert::notNull($user);

        $game = $user->currentGame();

        if ($game) {
            $this->turnService->finishGame($game);
        }

        $language = $game
            ? $game->language()
            : $this->languageService->getDefaultLanguage();

        $newGame = $this->gameService->createGameFor($user, $language);

        $firstTurn = $newGame
            ? $newGame->turns()->first()
            : null;

        return Response::json(
            $response,
            [
                'question' => null,
                'answer' => null,
                'new' => $this->serializer->serializeTurn($firstTurn)
            ]
        );
    }
}
