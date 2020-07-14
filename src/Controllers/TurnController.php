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
        $turns = $this->turnService->newPlayerTurn($game, $word, $user);

        Assert::minCount($turns, 1);

        $question = $turns[0];

        $answer = (count($turns) > 1) ? $turns[1] : null;

        $result = [
            'question' => $this->serializer->serializeTurn($question),
            'answer' => $answer ? $this->serializer->serializeTurn($answer) : null
        ];

        if (is_null($answer)) {
            $newGame = $this->gameService->newGame($language, $user);

            $firstTurn = $newGame
                ? $newGame->turns()->first()
                : null;

            $result['new'] = $firstTurn
                ? $this->serializer->serializeTurn($firstTurn)
                : null;
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

        $newGame = $this->gameService->newGame($language, $user);

        $firstTurn = $newGame
            ? $newGame->turns()->first()
            : null;

        return Response::json(
            $response,
            [
                'question' => null,
                'answer' => null,
                'new' => $firstTurn
                    ? $this->serializer->serializeTurn($firstTurn)
                    : null
            ]
        );
    }
}
