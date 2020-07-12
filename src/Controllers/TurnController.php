<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Models\Association;
use App\Models\Turn;
use App\Models\Word;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
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
use Webmozart\Assert\Assert;

class TurnController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;

    private AuthInterface $auth;
    private GameService $gameService;
    private TurnService $turnService;
    private WordService $wordService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->gameRepository;
        $this->languageRepository = $container->languageRepository;
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
            'question' => $this->serializeTurn($question),
            'answer' => $answer ? $this->serializeTurn($answer) : null,
            'new' => null,
        ];

        if (is_null($answer)) {
            $newGame = $this->gameService->newGame($language, $user);

            if ($newGame && $newGame->turns()->any()) {
                $result['new'] = $this->serializeTurn($newGame->turns()->first());
            }
        }

        return Response::json($response, $result);
    }

    /**
     * Play out of game context.
     */
    public function play(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $wordStr = $args['word'] ?? null;

        /** @var Language|null */
        $language = null;

        $langCode = $request->getQueryParam('lang', null);
        $prevWordId = $request->getQueryParam('prev_word_id', 0);

        if (strlen($langCode) > 0) {
            $language = $this->languageRepository->getByCode($langCode);

            if (is_null($language)) {
                throw new BadRequestException('Unknown language code.');
            }
        }

        $language ??= $this->languageService->getDefaultLanguage();

        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        $word = $this->wordRepository->findInLanguage($language, $wordStr);

        $prevWord = ($prevWordId > 0)
            ? $this->wordRepository->get($prevWordId)
            : null;

        $associations = $word
            ? $word
                ->publicAssociations()
                ->where(
                    fn (Association $a) => !$a->otherWord($word)->equals($prevWord)
                )
            : null;

        $wordAssociation = $word
            ? $word->associationByWord($prevWord)
            : null;

        $answerAssociation = $associations
            ? $associations->random()
            : null;

        $answer = $answerAssociation
            ? $answerAssociation->otherWord($word)
            : $this->languageService->getRandomPublicWord($language);

        $wordResponse = ['word' => $wordStr];

        if (strlen($wordStr) > 0) {
            $wordResponse['is_valid'] = $this->wordService->isWordValid($wordStr);
        }

        if ($word) {
            $wordResponse = $this->serialize(
                $wordResponse,
                $word,
                $wordAssociation
            );
        }

        /** @var array|null */
        $answerResponse = null;

        if ($answer) {
            $answerResponse = $this->serialize(
                ['word' => $answer->word],
                $answer,
                $answerAssociation
            );
        }

        $result = [
            'question' => $wordStr ? $wordResponse : null,
            'answer' => $answerAssociation ? $answerResponse : null,
        ];

        if (is_null($answerAssociation)) {
            $result['new'] = $answerResponse;
        }

        return Response::json($response, $result);
    }

    private function serializeTurn(Turn $turn) : array
    {
        return $this->serialize(
            [
                'game' => [
                    'id' => $turn->gameId,
                    'url' => $turn->game()->url()
                ],
                'turn_id' => $turn->getId(),
                'word' => $turn->word()->word,
                'is_ai' => $turn->isAiTurn()
            ],
            $turn->word(),
            $turn->association()
        );
    }

    private function serialize(array $array, Word $word, ?Association $association) : array
    {
        $array['id'] = $word->getId();
        $array['url'] = $this->linker->abs($word->url());

        if ($association) {
            $array['association'] = [
                'id' => $association->getId(),
                'is_approved' => $association->isApproved(),
                'url' => $this->linker->abs($association->url()),
            ];
        }

        return $array;
    }
}
