<?php

namespace App\Controllers;

use App\Exceptions\TurnException;
use App\Models\Association;
use App\Models\TelegramUser;
use App\Models\Turn;
use App\Models\User;
use App\Models\Validation\AgeValidation;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Semantics\Definition\DefinitionEntry;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TelegramUserService;
use App\Services\TurnService;
use App\Services\WordService;
use Exception;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Response;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Text;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class TelegramBotController
{
    const COMMAND_START = '/start';

    private SettingsProviderInterface $settingsProvider;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    private UserRepositoryInterface $userRepository;

    private GameService $gameService;
    private LanguageService $languageService;
    private TelegramUserService $telegramUserService;
    private TurnService $turnService;
    private WordService $wordService;

    private ValidatorInterface $validator;
    private AgeValidation $ageValidation;

    private string $languageCode;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        GameService $gameService,
        LanguageService $languageService,
        TelegramUserService $telegramUserService,
        TurnService $turnService,
        WordService $wordService,
        ValidatorInterface $validator,
        AgeValidation $ageValidation
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;
        $this->translator = $translator;

        $this->userRepository = $userRepository;

        $this->gameService = $gameService;
        $this->languageService = $languageService;
        $this->telegramUserService = $telegramUserService;
        $this->turnService = $turnService;
        $this->wordService = $wordService;

        $this->validator = $validator;
        $this->ageValidation = $ageValidation;

        $this->languageCode = 'ru';
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $data = $request->getParsedBody();

        if (!empty($data)) {
            $logEnabled = $this->settingsProvider->get('telegram.bot_log', false);

            if ($logEnabled === true) {
                $this->logger->info('Got request', $data);
            }
        }

        $message = $data['message'] ?? null;

        if ($this->isValidMessage($message)) {
            $processed = $this->processMessage($message, $response);

            return Response::json($response, $processed);
        } elseif ($this->isAddedToChat($data)) {
            $processed = $this->processAddedToChat($data, $response);

            return Response::json($response, $processed);
        }

        return $response;
    }

    private function isAddedToChat(array $data): bool
    {
        $chatId = $data['my_chat_member']['chat']['id'] ?? null;

        return $chatId !== null;
    }

    private function processAddedToChat(array $data): array
    {
        $chat = $data['my_chat_member']['chat'];

        Assert::notNull($chat);

        $chatId = $chat['id'] ?? null;
        $chatTitle = $chat['title'] ?? null;

        Assert::notNull($chatId);

        $result = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
        ];

        $tgUser = $this->telegramUserService->getOrCreateTelegramUser([
            'id' => $chatId,
            'first_name' => $chatTitle,
        ]);

        Assert::true($tgUser->isValid());

        $answerParts = $this->getAnswer($tgUser, self::COMMAND_START);

        $result['text'] = $this->buildAnswerFromParts($answerParts);

        return $result;
    }

    private function isValidMessage(?array $message): bool
    {
        $text = $message['text'] ?? null;

        return $text !== null;
    }

    private function processMessage(array $message): array
    {
        $result = [];

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        Assert::notNull($text);

        $tgUser = $this->telegramUserService->getOrCreateTelegramUser($message['chat']);

        Assert::true($tgUser->isValid());

        $result = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
        ];

        $text = trim($text);

        if (strlen($text) === 0) {
            $answer = '🧾 Я понимаю только сообщения с текстом.';
        } else {
            try {
                $answerParts = $this->getAnswer($tgUser, $text);
                $answer = $this->buildAnswerFromParts($answerParts);
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $answer = 'Что-то пошло не так. 😐';
            }
        }

        $result['text'] = $answer;

        return $result;
    }

    private function buildAnswerFromParts(array $parts): string
    {
        return implode(PHP_EOL . PHP_EOL, $parts);
    }

    /**
     * @return string[]
     */
    private function getAnswer(TelegramUser $tgUser, string $text): array
    {
        if (strpos($text, self::COMMAND_START) === 0) {
            return $this->startCommand($tgUser);
        }

        $user = $tgUser->user();

        if (!$user->hasAge()) {
            return $this->readAge($tgUser, $text);
        }

        if (strpos($text, '/skip') === 0) {
            return $this->skipCommand($tgUser);
        }

        if (strpos($text, '/what') === 0) {
            $commandText = $this->extractCommandText($text);
            return $this->whatCommand($tgUser, $commandText);
        }

        if (strpos($text, '/say') === 0) {
            $text = $this->extractCommandText($text);

            if (strlen($text) === 0) {
                return [
                    'При использовании команды "say" напишите слово через пробел.'
                ];
            }
        }

        return $this->sayWord($tgUser, $text);
    }

    private function extractCommandText(string $command): string
    {
        $chunks = explode(' ', $command);

        array_shift($chunks);

        return implode(' ', $chunks);
    }

    /**
     * @return string[]
     */
    private function startCommand(TelegramUser $tgUser): array
    {
        $user = $tgUser->user();

        $greetings = [];

        if ($tgUser->isChat()) {
            $greetings[] = 'Здравствуйте, люди! Спасибо, что добавили меня в свой чат. 🤖';
            $greetings[] = 'Чтобы сказать слово, ответьте на мое сообщение или используйте команду "say".';
        } else {
            $greeting = $tgUser->isNew() ? 'Добро пожаловать' : 'С возвращением';
            $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

            $greetings[] = $greeting;
        }

        if (!$user->hasAge()) {
            return [
                ...$greetings,
                ...$this->askAge()
            ];
        }

        return [
            ...$greetings,
            ...$this->startGame($tgUser)
        ];
    }

    /**
     * @return string[]
     */
    private function readAge(TelegramUser $tgUser, string $text): array
    {
        $validationData = ['age' => $text];
        $rules = $this->ageValidation->getRules($validationData);

        $validationResult = $this
            ->validator
            ->validateArray($validationData, $rules);

        $ageIsOk = $validationResult->isSuccess();

        if (!$ageIsOk) {
            return [
                'Вы написали что-то не то. 🤔',
                ...$this->askAge()
            ];
        }

        $user = $tgUser->user();
        $user->age = intval($text);
        $this->userRepository->save($user);

        return [
            'Спасибо, ваш возраст сохранен. 👌',
            ...$this->startGame($tgUser)
        ];
    }

    /**
     * @return string[]
     */
    private function askAge(): array
    {
        return [
            'Пожалуйста, укажите ваш возраст (цифрами):'
        ];
    }

    /**
     * @return string[]
     */
    private function skipCommand(TelegramUser $tgUser): array
    {
        $user = $tgUser->user();
        $game = $user->currentGame();

        Assert::notNull($game);

        $this->turnService->finishGame($game);

        return $this->newGame(
            $user,
            'Сдаетесь? 😏 Ок, начинаем заново!'
        );
    }

    /**
     * @return string[]
     */
    private function whatCommand(TelegramUser $tgUser, string $text): array
    {
        $user = $tgUser->user();
        $game = $user->currentGame();

        Assert::notNull($game);

        /** @var Word|null $word */
        $word = null;

        if (strlen($text) > 0) {
            // find word
            $language = $this->languageService->getCurrentLanguageFor($user);

            $word = $this->languageService->findWord($language, $text);

            if ($word === null) {
                return [
                    sprintf('Я не знаю, что такое <b>%s</b>.', $text),
                ];
            }
        } else {
            /** @var Turn $lastTurn */
            $lastTurn = $game->turns()->first();

            if ($lastTurn === null) {
                return ['Вы о чем?'];
            }

            $word = $lastTurn->word();
        }

        Assert::notNull($word);

        $parsedDefinition = $this->wordService->getParsedTransitiveDefinition($word);

        $noDefinition = ['Определение отсутствует.'];

        if ($parsedDefinition === null) {
            return $noDefinition;
        }

        $defText = [];

        $defEntries = $parsedDefinition->entries();

        for ($index = 0; $index < $defEntries->count(); $index++) {
            /** @var DefinitionEntry $defEntry */
            $defEntry = $defEntries[$index];

            $defTitle = '<b>' . mb_strtoupper($parsedDefinition->word()->word) . '</b>';

            if ($defEntries->count() > 1) {
                $defTitle .= ' (' . ($index + 1) . ')';
            }

            if ($defEntry->partOfSpeech() !== null) {
                $pos = $this->translator->translate(
                    $defEntry->partOfSpeech()->shortName(),
                    $this->languageCode
                );

                $defTitle .= ' <i>' . $pos . '</i>';
            }

            $defText[] = $defTitle;

            $defEntryLines = [];

            $defEntryDefs = $defEntry->definitions();

            for ($subIndex = 0; $subIndex < $defEntryDefs->count(); $subIndex++) {
                /** @var string $defEntryDef */
                $defEntryDef = $defEntryDefs[$subIndex];

                if ($defEntryDefs->count() > 1) {
                    $defEntryDef = ($subIndex + 1) . '. ' . $defEntryDef;
                }

                $defEntryLines[] = $defEntryDef;
            }

            $defText[] = Text::join($defEntryLines);
        }

        return $defText;
    }

    /**
     * @return string[]
     */
    private function sayWord(TelegramUser $tgUser, string $text): array
    {
        $user = $tgUser->user();
        $game = $user->currentGame();

        Assert::notNull($game);

        try {
            $turns = $this->gameService->makeTurn($user, $game, $text);
        } catch (ValidationException $vEx) {
            return [
                '❌ ' . $vEx->firstError()
            ];
        } catch (TurnException $tEx) {
            return [
                '❌ ' . $tEx->getTranslatedMessage($this->translator)
            ];
        }

        if ($turns->count() > 1) {
            // continuing current game
            return $this->turnsToParts(
                $turns->first(),
                $turns->second()
            );
        }

        // no answer, starting new game
        return $this->newGame(
            $user,
            'У меня нет ассоциаций. 😥 Начинаем заново!'
        );
    }

    /**
     * @return string[]
     */
    private function startGame(TelegramUser $tgUser): array
    {
        $user = $tgUser->user();
        $isNewUser = $tgUser->isNew();

        $game = $this->gameService->getOrCreateGameFor($user);

        Assert::notNull($game);

        return [
            $isNewUser ? 'Начинаем игру...' : 'Продолжаем игру...',
            ...$this->turnsToParts(
                $game->beforeLastTurn(),
                $game->lastTurn()
            )
        ];
    }

    /**
     * @return string[]
     */
    private function newGame(User $user, string $message): array
    {
        $newGame = $this->gameService->createGameFor($user);

        return $this->turnsToParts(
            null,
            $newGame->lastTurn(),
            $message
        );
    }

    /**
     * @return string[]
     */
    private function turnsToParts(
        ?Turn $question,
        ?Turn $answer,
        ?string $noQuestionMessage = null
    ): array
    {
        if (is_null($answer)) {
            return [
                'Мне нечего сказать. 😥 Начинайте вы.'
            ];
        }

        Assert::true($answer->isAiTurn());

        $answerWordStr = $this->turnStr($answer);

        $commands = [];

        $parsedDefinition = $this->wordService->getParsedTransitiveDefinition(
            $answer->word()
        );

        if ($parsedDefinition) {
            $commands[] = sprintf(
                '/what Что такое "%s"?',
                $answer->word()->word
            );
        }

        // $commands[] = '/skip Другое слово';

        if ($question === null) {
            return array_filter(
                [
                    $noQuestionMessage,
                    $answerWordStr,
                    ...$commands
                ]
            );
        }

        $questionWordStr = $this->turnStr($question);

        $association = $answer->association();

        $sign = $association
            ? $association->sign()
            : Association::DEFAULT_SIGN;

        $associationStr = $questionWordStr . ' ' . $sign . ' ' . $answerWordStr;

        return [
            $associationStr,
            ...$commands
        ];
    }

    private function turnStr(Turn $turn): string
    {
        return '<b>' . mb_strtoupper($turn->word()->word) . '</b>';
    }
}
