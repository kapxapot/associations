<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\Language;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GameController extends Controller
{
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $game = Game::get($id);

        $user = $this->auth->getUser();

        if (is_null($game) || is_null($user)) {
            return $this->notFound($request, $response);
        }

        $canSeeAllGames = $this->access->checkRights('games', 'edit');
        $hasPlayer = $game->hasPlayer($user);

        if (!$canSeeAllGames && !$hasPlayer) {
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
        
        return $this->render($response, 'main/games/item.twig', $params);
    }
    
    public function start(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
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
        
        return Response::json($response, [
            'message' => $this->translate('New game started.'),
        ]);
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
        
        return Response::json($response, [
            'message' => $this->translate('Game finished.'),
        ]);
    }
}
