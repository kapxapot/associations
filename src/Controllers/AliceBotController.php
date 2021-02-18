<?php

namespace App\Controllers;

use App\Models\Association;
use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
use App\Models\DTO\MetaTurn;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use App\Services\WordService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AliceBotController
{
    use LoggerAwareTrait;

    private WordRepositoryInterface $wordRepository;

    private LanguageService $languageService;
    private WordService $wordService;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService,
        WordService $wordService,
        LoggerInterface $logger
    )
    {
        $this->wordRepository = $wordRepository;

        $this->languageService = $languageService;
        $this->wordService = $wordService;

        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            $aliceRequest = new AliceRequest($data);
            $aliceResponse = $this->getResponse($aliceRequest);

            return Response::json(
                $response,
                $this->buildMessage($aliceRequest, $aliceResponse)
            );
        } catch (Exception $ex) {
            $this->logEx($ex);
        }

        return Response::text($response, 'Error');
    }

    private function getResponse(AliceRequest $request): AliceResponse
    {
        $question = $request->command;

        $isNewSession = $request->isNewSession;

        if (!$isNewSession && strlen($question) === 0) {
            return new AliceResponse('Повторите, пожалуйста');
        }

        $answerParts = [];

        if ($isNewSession) {
            $answerParts[] = 'Привет! Поиграем в Ассоциации? Говорим по очереди слово, которое ассоциируется с предыдущим. Я начинаю:';
        }

        $prevWordId = $request->var('prev_word_id');

        $turn = $this->getWordFor($question, $prevWordId);

        if ($turn->word() === null) {
            $answerParts[] = 'У меня нет слов';

            return new AliceResponse(
                Text::join($answerParts, ' ')
            );
        }

        if (!$isNewSession && $turn->association() === null) {
            $answerParts[] = 'У меня нет ассоциаций. Начинаем заново:';
        }

        $answerParts[] = mb_strtoupper($turn->word()->word);

        $response = new AliceResponse(
            Text::join($answerParts, ' ')
        );

        if ($request->hasUser()) {
            $response->withUserVar('prev_word_id', $turn->word()->getId());
        } else {
            $response->withApplicationVar('prev_word_id', $turn->word()->getId());
        }

        return $response;
    }

    private function getWordFor(string $question, ?int $prevWordId): MetaTurn
    {
        $language = $this->languageService->getDefaultLanguage();

        $wordStr = $this->languageService->normalizeWord($language, $question);

        $word = $this->wordRepository->findInLanguage($language, $wordStr);

        $prevWord = ($prevWordId > 0)
            ? $this->wordRepository->get($prevWordId)
            : null;

        // don't reveal invisible words
        $user = null;
        $word = $this->wordService->purgeFor($word, $user);
        $prevWord = $this->wordService->purgeFor($prevWord, $user);

        $answerAssociation = $word
            ? $word
                ->publicAssociations()
                ->where(
                    fn (Association $a) => !$a->otherWord($word)->equals($prevWord)
                )
                ->random()
            : null;

        $answer = $answerAssociation
            ? $answerAssociation->otherWord($word)
            : $this->languageService->getRandomPublicWord($language);

        return new MetaTurn($answerAssociation, $answer);
    }

    private function buildMessage(AliceRequest $request, AliceResponse $response): array
    {
        $data = [
            'response' => [
                'text' => $response->text,
                'end_session' => $response->endSession,
            ],
            'version' => '1.0',
        ];

        if ($request->hasUser()) {
            $data['user_state_update'] = $response->userState;
        } else {
            $data['application_state'] = $response->applicationState;
        }

        return $data;
    }
}
