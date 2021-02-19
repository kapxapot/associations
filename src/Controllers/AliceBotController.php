<?php

namespace App\Controllers;

use App\Models\Association;
use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
use App\Models\DTO\MetaTurn;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use App\Services\WordService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AliceBotController
{
    use LoggerAwareTrait;

    private const VAR_PREV_WORD = 'prev_word_id';

    private const BUTTON_HELP = 'help';
    private const BUTTON_CAN = 'can';

    private WordRepositoryInterface $wordRepository;

    private LanguageService $languageService;
    private WordService $wordService;

    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService,
        WordService $wordService,
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger
    )
    {
        $this->wordRepository = $wordRepository;

        $this->languageService = $languageService;
        $this->wordService = $wordService;

        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            if (!empty($data)) {
                $logEnabled = $this->settingsProvider->get('alice.bot_log', false);

                if ($logEnabled === true) {
                    $this->log('Got request', $data);
                }
            }

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
        $buttonPayload = $request->buttonPayload();
        $isNewSession = $request->isNewSession;

        if ($isNewSession) {
            return $this->answerWithAnyWord(
                $request,
                'Привет! Поиграем в Ассоциации? Говорим по очереди слово, которое ассоциируется с предыдущим. Я начинаю:'
            );
        }

        if ($buttonPayload === self::BUTTON_HELP) {
            return new AliceResponse('Говорим по очереди слово, которое ассоциируется с предыдущим. Желательно использовать существительные. Скажите \'другое слово\' или \'пропустить\', если не хотите отвечать на слово. В ассоциации также можно играть у нас на сайте associ.ru и в нашем Telegram-боте t.me/AssociRuBot.');
        }

        if ($buttonPayload === self::BUTTON_CAN) {
            return new AliceResponse('Я умею играть в ассоциации!');
        }

        if (strlen($question) === 0) {
            return new AliceResponse('Повторите, пожалуйста');
        }

        $skipPhrases = [
            'другое слово',
            'пропустить'
        ];

        if (in_array(mb_strtolower($question), $skipPhrases)) {
            return $this->answerWithAnyWord(
                $request,
                'Хорошо. Начинаем заново:'
            );
        }

        $prevWordId = $request->var(self::VAR_PREV_WORD);

        $turn = $this->getWordFor($question, $prevWordId);
        $word = $turn->word();

        return ($word !== null)
            ? $this->answerWithWord($request, $word)
            : $this->answerWithAnyWord($request, 'У меня нет ассоциаций. Начинаем заново:');
    }

    private function answerWithAnyWord(
        AliceRequest $request,
        string ...$answerParts
    ): AliceResponse
    {
        $word = $this->getAnyWord($request);

        return $this->answerWithWord($request, $word, ...$answerParts);
    }

    private function answerWithWord(
        AliceRequest $request,
        ?Word $word,
        string ...$answerParts
    ): AliceResponse
    {
        $answerParts[] = ($word !== null)
            ? mb_strtoupper($word->word)
            : 'У меня нет слов';

        $response = new AliceResponse(
            Text::join($answerParts, ' ')
        );

        if ($word !== null && $request->hasUser()) {
            $response->withUserVar(self::VAR_PREV_WORD, $word->getId());
        } else {
            $response->withApplicationVar(self::VAR_PREV_WORD, $word->getId());
        }

        return $response;
    }

    private function getWordFor(?string $question, ?int $prevWordId): MetaTurn
    {
        $word = $this->findWord($question);

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
            : null;

        return new MetaTurn($answerAssociation, $answer);
    }

    private function getAnyWord(?AliceRequest $request = null): ?Word
    {
        $language = $this->getLanguage();

        $word = ($request !== null)
            ? $this->findWord($request->command)
            : null;

        return $this->languageService->getRandomPublicWord($language, $word);
    }

    private function findWord(?string $wordStr): ?Word
    {
        $language = $this->languageService->getDefaultLanguage();
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        return $this->wordRepository->findInLanguage($language, $wordStr);
    }

    private function getLanguage(): Language
    {
        return $this->languageService->getDefaultLanguage();
    }

    private function buildMessage(AliceRequest $request, AliceResponse $response): array
    {
        $data = [
            'response' => [
                'text' => $response->text,
                'end_session' => $response->endSession,
                'buttons' => [
                    [
                        'title' => 'Помощь',
                        'payload' => self::BUTTON_HELP,
                        'hide' => true,
                    ],
                    [
                        'title' => 'Что ты умеешь?',
                        'payload' => self::BUTTON_CAN,
                        'hide' => true,
                    ],
                ],
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
