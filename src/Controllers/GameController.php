<?php

namespace App\Controllers;

use App\Models\Language;
use App\Models\Turn;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\AssociationService;
use App\Services\GameService;
use App\Services\WordService;
use Plasticode\Core\Request;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GameController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;

    private AssociationService $associationService;
    private GameService $gameService;
    private WordService $wordService;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameRepository = $container->get(GameRepositoryInterface::class);
        $this->languageRepository = $container->get(LanguageRepositoryInterface::class);

        $this->associationService = $container->get(AssociationService::class);
        $this->gameService = $container->get(GameService::class);
        $this->wordService = $container->get(WordService::class);

        $this->notFoundHandler = $container->get(NotFoundHandlerInterface::class);
    }

    /**
     * Get game by id.
     */
    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $debug = $this->isDebug($request);

        $game = $this->gameRepository->get($id);
        $user = $this->auth->getUser();

        if (is_null($game) || is_null($user)) {
            return ($this->notFoundHandler)($request, $response);
        }

        $canSeeAllGames = $user->policy()->canSeeAllGames();
        $isPlayer = $game->hasPlayer($user);

        if (!$canSeeAllGames && !$isPlayer) {
            return ($this->notFoundHandler)($request, $response);
        }

        if (Request::acceptsJson($request)) {
            return Response::json(
                $response,
                [
                    'id' => $game->getId(),
                    'url' => $game->url(),
                    'history' => $game->turns()->reverse()->map(
                        fn (Turn $t) => $this->serializer->serializeTurn($t)
                    )
                ]
            );
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

    /**
     * Play out of game context.
     */
    public function play(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $data = $request->getParsedBody();

        $wordStr = $data['word'] ?? null;

        /** @var Language|null */
        $language = null;

        $langCode = $data['lang_code'] ?? null;
        $prevWordId = $data['prev_word_id'] ?? 0;

        if (strlen($langCode) > 0) {
            $language = $this->languageRepository->getByCode($langCode);

            if ($language === null) {
                throw new BadRequestException('Unknown language code.');
            }
        }

        $language ??= $this->languageService->getDefaultLanguage();

        $word = $this->languageService->findWord($language, $wordStr);

        $prevWord = ($prevWordId > 0)
            ? $this->wordRepository->get($prevWordId)
            : null;

        // don't reveal invisible words
        $word = $this->wordService->purgeFor($word, $user);
        $prevWord = $this->wordService->purgeFor($prevWord, $user);

        $wordResponse = [
            'word' => $wordStr,
            'is_valid' => $this->wordService->isWordValid($wordStr)
        ];

        if ($word) {
            $wordAssociation = $prevWord
                ? $this->associationService->getByPair($prevWord, $word)
                : null;

            $wordResponse = $this->serializer->serializeRaw(
                $wordResponse,
                $word,
                $wordAssociation
            );
        }

        // getting an answer

        /** @var array|null */
        $answerResponse = null;

        $answerTurn = $this->gameService->playPseudoTurn($language, $word, $prevWord);

        $answer = $answerTurn->word();
        $answerAssociation = $answerTurn->association();

        if ($answer) {
            $answerResponse = $this->serializer->serializeRaw(
                [
                    'word' => $answer->word,
                    'is_not_native' => $answerAssociation
                        && $word
                        && !$answerAssociation->hasWord($word),
                ],
                $answer,
                $answerAssociation
            );
        }

        $result = [
            'question' => $wordStr ? $wordResponse : null,
            'answer' => $answerAssociation ? $answerResponse : null,
        ];

        if ($answerAssociation === null) {
            $result['new'] = $answerResponse;
        }

        return Response::json($response, $result);
    }
}
