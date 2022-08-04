<?php

namespace App\Controllers;

use App\Exceptions\TurnException;
use App\Models\Turn;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Services\GameService;
use App\Services\TurnService;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Exceptions\InvalidResultException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

class TurnController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;

    private GameService $gameService;
    private TurnService $turnService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->get(GameRepositoryInterface::class);
        $this->turnRepository = $container->get(TurnRepositoryInterface::class);

        $this->gameService = $container->get(GameService::class);
        $this->turnService = $container->get(TurnService::class);
    }

    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $this->auth->getUser();

        // validate game
        $gameId = $data['game_id'] ?? null;
        $game = $this->gameRepository->get($gameId);

        if ($game === null) {
            throw new NotFoundException('Game not found.');
        }

        $currentGame = $user->currentGame();

        if (!$game->equals($currentGame)) {
            throw new BadRequestException(
                'Game is finished. Please, reload the page.'
            );
        }

        // validate prev turn
        $prevTurnId = $data['prev_turn_id'] ?? null;
        $prevTurn = $this->turnRepository->get($prevTurnId);

        if (!$this->gameService->validateLastTurn($game, $prevTurn)) {
            throw new BadRequestException(
                'Game turn is not correct. Please, reload the page.'
            );
        }

        // validate word
        $wordStr = $data['word'] ?? null;

        try {
            $turns = $this->gameService->makeTurn($user, $game, $wordStr);
        } catch (TurnException $tEx) {
            throw new BadRequestException(
                $tEx->getTranslatedMessage($this->translator)
            );
        } catch (InvalidResultException $ex) {
            throw new BadRequestException($ex->getMessage());
        }

        /** @var Turn */
        $question = $turns->first();
        $answer = $turns->second();

        $result = [
            'question' => $this->serializer->serializeTurn($question),
            'answer' => $this->serializer->serializeTurn($answer)
        ];

        if ($answer === null) {
            $newGame = $this->gameService->createNewGameFor($user);

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

        $this->turnService->finishGameFor($user);

        $newGame = $this->gameService->createNewGameFor($user);

        $lastTurn = $newGame
            ? $newGame->lastTurn()
            : null;

        return Response::json(
            $response,
            [
                'question' => null,
                'answer' => null,
                'new' => $this->serializer->serializeTurn($lastTurn)
            ]
        );
    }
}
