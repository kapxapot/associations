<?php

namespace App\Controllers;

use Plasticode\Core\Core;
use Plasticode\Exceptions\BadRequestException;
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

        if (!$this->gameService->validateLastTurn($prevTurn)) {
            throw new BadRequestException('Game turn is not correct. Please, reload the page.');
        }

        $rules['word'] = $this
            ->rule('text')
            ->length($this->config->wordMinLength(), $this->config->wordMaxLength())
            ->wordIsValid() // v
            ->wordIsNotRepetitive($data['game_id']); // v
        
        $wordStr = $data['word'];
        
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);
        
        $word = Word::findInLanguage($language, $wordStr)
            ?? $this->wordService->create($language, $wordStr, $user);
        
        if ($word === null) {
            throw new ApplicationException('Word can\'t be found or added.');
        }
        
        // association_id
        if ($game->lastTurn() !== null) {
            $association = Association::getByPair($game->lastTurnWord(), $word, $language)
                ?? $this->associationService->create($game->lastTurnWord(), $word, $user, $language);
            
            if ($association === null) {
                throw new ApplicationException('Association can\'t be found or added.');
            }
        }

        $turn = Turn::create();
        $turn->userId = $user->getId();
        $turn->languageId = $language->getId();
        $turn->wordId = $word->getId();
        $turn->associationId = $association->getId();
        $turn->save();
        
        $this->turnService->processPlayerTurn($turn);

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
