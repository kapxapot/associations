<?php

namespace App\Controllers;

use Plasticode\Core\Core;
use Plasticode\Exceptions\NotFoundException;

use App\Models\Game;
use App\Models\Language;

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
            throw new \Exception('Game is already on.');
        };

        $game = Game::create();
        $game->languageId = $languageId;
        $game->userId = $user->getId();
        $game->save();

        $this->gameService->startGame($game);
        
        return Core::json($response, [
            'message' => $this->translate('New game started.'),
        ]);
    }

    public function turn($request, $response, $args)
    {
        
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
