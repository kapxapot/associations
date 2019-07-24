<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\Turn;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class TurnController extends Controller
{
    public function create(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->getUser();

        // validate game
        $gameId = $request->getParam('game_id');
        $game = Game::get($gameId);

        if ($game === null) {
            throw new NotFoundException('Game not found.');
        }

        if ($game->getId() !== $user->currentGame()->getId()) {
            throw new BadRequestException('Game is finished. Please, reload the page.');
        }

        $language = $game->language();

        // validate prev turn
        $prevTurnId = $request->getParam('prev_turn_id');
        $prevTurn = Turn::get($prevTurnId);

        if (!$this->gameService->validateLastTurn($game, $prevTurn)) {
            throw new BadRequestException('Game turn is not correct. Please, reload the page.');
        }

        // validate word
        $wordStr = $request->getParam('word');
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        $this->wordService->validateWord($wordStr);

        if (!$this->turnService->validatePlayerTurn($game, $wordStr)) {
            throw new BadRequestException('Word is already used in this game.');
        }
        
        // get word
        $word = $this->wordService->getOrCreate($language, $wordStr, $user);

        // new turn
        $this->turnService->newPlayerTurn($game, $word, $user);
        
        return Response::json($response, [
            'message' => $this->translate('Turn successfully done.'),
        ]);
    }
}
