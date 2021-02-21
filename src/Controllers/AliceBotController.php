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
use Plasticode\Collections\Generic\StringCollection;
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

    private const COMMAND_HELP = 'помощь';
    private const COMMAND_CAN = 'что ты умеешь';

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
        $isNewSession = $request->isNewSession;

        if ($isNewSession) {
            return $this->answerWithAnyWord(
                $request,
                'Привет! Поиграем в Ассоциации? Говорим по очереди слово, которое ассоциируется с предыдущим. Я начинаю:'
            );
        }

        if (strlen($question) === 0) {
            return new AliceResponse('Повторите, пожалуйста');
        }

        $prevWordId = $request->var(self::VAR_PREV_WORD);
        $prevWord = $this->wordRepository->get($prevWordId);

        $helpCommands = [
            self::COMMAND_HELP,
            self::COMMAND_CAN,
        ];

        if (in_array($question, $helpCommands)) {
            return $this
                ->answerWithWord(
                    $request,
                    $prevWord,
                    'В игре в ассоциации Алиса и игрок говорят по очереди слово, которое ассоциируется с предыдущим. Желательно использовать существительные. Скажите \'другое слово\' или \'пропустить\', если не хотите отвечать на слово.',
                    'Продолжаем.',
                    'Мое слово:'
                )
                ->withStateFrom($request);
        }

        $skipPhrases = [
            'другое слово',
            'пропустить',
        ];

        if (in_array($question, $skipPhrases)) {
            return $this->answerWithAnyWord(
                $request,
                'Хорошо. Начинаем заново:'
            );
        }

        $turn = $this->getWordFor($question, $prevWord);

        return $this->turnToAnswer($request, $turn);
    }

    private function turnToAnswer(AliceRequest $request, MetaTurn $turn): AliceResponse
    {
        $questionWord = $turn->prevWord();
        $answerWord = $turn->word();

        $answerParts = [];

        if ($questionWord !== null && $questionWord->isMature()) {
            $answerParts[] = $this->randomString(
                'Ой! Надеюсь, рядом нет детей.',
                'Вы вгоняете меня в краску.',
                'Ну у вас и словечки!',
                'Хм... Как скажете.'
            );
        }

        if ($answerWord === null) {
            $answerParts[] = $this->randomString(
                'У меня нет ассоциаций.',
                'Мне нечего сказать.',
                'Я в тупике.',
                'Я сдаюсь.'
            );

            $answerParts[] = 'Начинаем заново:';

            return $this->answerWithAnyWord($request, ...$answerParts);
        }

        return $this->answerWithWord($request, $answerWord, ...$answerParts);
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

        if ($word !== null) {
            $response->withVarBy($request, self::VAR_PREV_WORD, $word->getId());
        }

        return $response;
    }

    private function getWordFor(?string $question, ?Word $prevWord): MetaTurn
    {
        $word = $this->findWord($question);

        $answerAssociation = $word
            ? $word->randomPublicAssociation($prevWord)
            : null;

        $answer = $answerAssociation
            ? $answerAssociation->otherWord($word)
            : null;

        return new MetaTurn($answerAssociation, $answer, $word);
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

    private function randomString(string ...$strings): ?string
    {
        return StringCollection::make($strings)->random();
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