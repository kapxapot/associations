<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use App\Models\Association;
use App\Models\Word;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\GameService;
use App\Services\TurnService;
use App\Services\WordService;
use Plasticode\Auth\Access;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;
use Webmozart\Assert\Assert;

class GameController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;

    private GameService $gameService;
    private TurnService $turnService;
    private WordService $wordService;

    private Access $access;
    private AuthInterface $auth;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->gameRepository;
        $this->languageRepository = $container->languageRepository;

        $this->gameService = $container->gameService;
        $this->turnService = $container->turnService;
        $this->wordService = $container->wordService;

        $this->access = $container->access;
        $this->auth = $container->auth;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function get(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $debug = $request->getQueryParam('debug', null) !== null;

        $game = $this->gameRepository->get($id);
        $user = $this->auth->getUser();

        if (is_null($game) || is_null($user)) {
            return ($this->notFoundHandler)($request, $response);
        }

        $canSeeAllGames = $this->access->checkActionRights('games', 'edit', $user);
        $isPlayer = $game->hasPlayer($user);

        if (!$canSeeAllGames && !$isPlayer) {
            return ($this->notFoundHandler)($request, $response);
        }

        $params = $this->buildParams(
            [
                'params' => [
                    'title' => $game->displayName(),
                    'game' => $game,
                    'disqus_id' => 'game' . $game->getId(),
                    'debug' => $debug,
                ],
            ]
        );

        return $this->render($response, 'main/games/item.twig', $params);
    }

    public function start(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $languageId = $request->getParam('language_id');
        $language = $this->languageRepository->get($languageId);

        if (is_null($language)) {
            throw new NotFoundException('Language not found.');
        }

        if ($user->currentGame() !== null) {
            throw new BadRequestException('Game is already on.');
        };

        $this->gameService->newGame($language, $user);

        return Response::json(
            $response,
            ['message' => $this->translate('New game started.')]
        );
    }

    public function finish(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        Assert::notNull($user);

        $game = $user->currentGame();

        /** @var string */
        $msg = null;

        if ($game !== null) {
            $this->turnService->finishGame($game);
            $msg = 'Game finished.';
        } else {
            $msg = 'No current game found.';
        }

        return Response::json(
            $response,
            ['message' => $this->translate($msg)]
        );
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
