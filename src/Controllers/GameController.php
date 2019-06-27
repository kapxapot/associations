<?php

namespace App\Controllers;

use Plasticode\Core\Core;
use Plasticode\Exceptions\BadRequestException;
use Plasticode\Exceptions\NotFoundException;

use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;

class GameController extends Controller
{
    public function get($request, $response, $args)
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $game = Game::get($id);

        if ($game === null) {
            return $this->notFound($request, $response);
        }

        $params = $this->buildParams([
            'params' => [
                'title' => $game->displayName(),
                'game' => $game,
                'disqus_id' => 'game' . $game->getId(),
                'debug' => $debug,
            ],
        ]);
        
        return $this->view->render($response, 'main/games/item.twig', $params);
    }
    
    public function start($request, $response, $args)
    {
        $user = $this->auth->getUser();

        $languageId = $request->getParam('language_id');
        $language = Language::get($languageId);

        if ($language === null) {
            throw new NotFoundException('Language not found.');
        }

        if ($user->currentGame() !== null) {
            throw new BadRequestException('Game is already on.');
        };

        $this->gameService->newGame($language, $user);
        
        return Core::json($response, [
            'message' => $this->translate('New game started.'),
        ]);
    }

    public function turn($request, $response, $args)
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
        
        return Core::json($response, [
            'message' => $this->translate('Turn successfully done.'),
        ]);
    }

    public function finish($request, $response, $args)
    {
        $user = $this->auth->getUser();
        
        if ($user) {
            $game = $user->currentGame();
            
            if ($game !== null) {
                $this->gameService->finishGame($game);
            }
        }

        return $response;
    }
}
