<?php

namespace App\Controllers;

use App\Bots\Answerers\UserAnswerer;
use App\Exceptions\TurnException;
use App\Models\Association;
use App\Models\DTO\PseudoTurn;
use App\Models\Interfaces\TurnInterface;
use App\Models\Language;
use App\Models\TelegramUser;
use App\Models\User;
use App\Models\Validation\AgeValidation;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Semantics\Definition\DefinitionEntry;
use App\Semantics\Word\Tokenizer;
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
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Text;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class TelegramBotController
{
    use LoggerAwareTrait;

    const COMMAND_START = '/start';

    const STATUS_LEFT = 'left';
    const STATUS_MEMBER = 'member';
    const STATUS_ADMINISTRATOR = 'administrator';

    private SettingsProviderInterface $settingsProvider;
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

    private Tokenizer $tokenizer;

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

        $this->withLogger($logger);

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

        $this->tokenizer = new Tokenizer();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $logEnabled = $this->settingsProvider->get('telegram.bot_log', false);

        $data = $request->getParsedBody();

        if (!empty($data) && $logEnabled) {
            $this->log('Got request', $data);
        }

        $message = $data['message'] ?? null;

        /** @var array|null $processed */
        $processed = null;

        if ($this->isValidMessage($message)) {
            $processed = $this->processMessage($message, $response);
        } elseif ($this->isMyChatMemberMessage($data)) {
            $processed = $this->processMyChatMemberMessage($data, $response);
        }

        if (!empty($processed)) {
            if ($logEnabled) {
                $this->log('Answer', $processed);
            }

            return Response::json($response, $processed);
        }

        return $response;
    }

    private function isValidMessage(?array $message): bool
    {
        $text = $message['text'] ?? null;

        return $text !== null;
    }

    private function isMyChatMemberMessage(array $data): bool
    {
        $chatId = $data['my_chat_member']['chat']['id'] ?? null;

        return $chatId !== null;
    }

    /**
     * - left -> member = greeting
     * - member -> administrator = marking as admin, halting game
     * - administrator -> member = unmarking as admin, continuing game
     */
    private function processMyChatMemberMessage(array $data): ?array
    {
        $myChatMember = $data['my_chat_member'];
        $chat = $myChatMember['chat'];

        Assert::notNull($chat);

        $chatId = $chat['id'] ?? null;
        $chatTitle = $chat['title'] ?? null;

        Assert::notNull($chatId);

        $oldChatMember = $myChatMember['old_chat_member'];
        $newChatMember = $myChatMember['new_chat_member'];

        $oldStatus = $oldChatMember['status'];
        $newStatus = $newChatMember['status'];

        // get or create a TelegramUser for the chat
        $tgUser = $this->telegramUserService->getOrCreateTelegramUser([
            'id' => $chatId,
            'first_name' => $chatTitle,
        ]);

        Assert::true($tgUser->isValid());

        if ($newStatus !== $oldStatus) {
            if ($newStatus === self::STATUS_ADMINISTRATOR) {
                $this->telegramUserService->markAsBotAdmin($tgUser);
            }

            if ($oldStatus === self::STATUS_ADMINISTRATOR) {
                $this->telegramUserService->unmarkAsBotAdmin($tgUser);
            }
        }

        /** @var string|null $answer */
        $answer = null;

        if ($oldStatus === self::STATUS_LEFT && $newStatus === self::STATUS_MEMBER) {
            // bot added to group chat
            $answerParts = $this->startCommand($tgUser);
            $answer = $this->joinParts($answerParts);
        } elseif ($oldStatus === self::STATUS_MEMBER && $newStatus === self::STATUS_ADMINISTRATOR) {
            // bot made an admin (bad!)
            $answer = 'Зря вы меня сделали админом. 😥 Игра остановлена и будет возобновлена, когда вы уберете меня из админов.';
        } elseif ($oldStatus === self::STATUS_ADMINISTRATOR && $newStatus === self::STATUS_MEMBER) {
            // bot made a member from an admin
            $greeting = [
                'Спасибо, что убрали меня из админов. 🤩 Мы снова можем играть!'
            ];

            $answerParts = $this->startCommand($tgUser, $greeting);
            $answer = $this->joinParts($answerParts);
        } else {
            // unknown action
            return null;
        }

        return $this->buildTelegramMessage($chatId, $answer);
    }

    private function processMessage(array $message): ?array
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        Assert::notNull($text);

        $tgUser = $this->telegramUserService->getOrCreateTelegramUser($message['chat']);

        Assert::true($tgUser->isValid());

        // if bot is made admin, ignore all messages
        if ($tgUser->isBotAdmin()) {
            return null;
        }

        $text = trim($text);

        $answer = $this->validateText($text);

        if ($answer === null) {
            try {
                $answerParts = $this->getAnswer($tgUser, $text);
                $answer = $this->joinParts($answerParts);
            } catch (Exception $ex) {
                $this->logEx($ex);
                $answer = 'Что-то пошло не так. 😐';
            }
        }

        return $this->buildTelegramMessage($chatId, $answer);
    }

    private function validateText(string $text): ?string
    {
        if (strlen($text) === 0) {
            return '🧾 Я понимаю только сообщения с текстом.';
        }

        $tokens = $this->tokenizer->tokenize($text);

        if (count($tokens) > UserAnswerer::MAX_TOKENS) {
            return 'Давайте не больше ' . UserAnswerer::WORD_LIMIT . ' сразу.';
        }

        return null;
    }

    private function joinParts(array $parts): string
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
        $chunks = $this->tokenizer->tokenize($command);

        array_shift($chunks);

        return $this->tokenizer->join($chunks);
    }

    /**
     * @return string[]
     */
    private function startCommand(TelegramUser $tgUser, ?array $customGreeting = null): array
    {
        $greeting = $customGreeting ?? [];

        if (empty($greeting)) {
            if ($tgUser->isChat()) {
                $greeting = [
                    'Здравствуйте, люди! Спасибо, что добавили меня в свой чат. 🤖',
                    'Чтобы сказать слово, ответьте на мое сообщение или используйте команду "say".',
                    '⚠ Внимание! Не делайте меня админом, иначе игра будет остановлена.',
                ];
            } else {
                $satulation = $tgUser->isNew() ? 'Добро пожаловать' : 'С возвращением';
                $name = $tgUser->privateName();

                $greeting[] = [
                    sprintf('%s, <b>%s</b>!', $satulation, $name)
                ];
            }
        }

        $user = $tgUser->user();

        if (!$user->hasAge()) {
            return [
                ...$greeting,
                ...$this->askAge()
            ];
        }

        return [
            ...$greeting,
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

        $this->turnService->finishGameFor($user);

        return $this->newGame(
            $tgUser,
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

        if (strlen($text) > 0) {
            $word = $this->languageService->findWord(
                $this->getLanguage($user),
                $text
            );

            if ($word === null) {
                return [
                    sprintf('Я не знаю, что такое <b>%s</b>.', $text),
                ];
            }
        } else {
            $lastTurn = $game->lastTurn();

            if ($lastTurn === null) {
                return ['Вы о чем?'];
            }

            $word = $lastTurn->word();
        }

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
            $tgUser,
            'У меня нет ассоциаций. 😥 Начинаем заново!'
        );
    }

    /**
     * @return string[]
     */
    private function startGame(TelegramUser $tgUser): array
    {
        $isDemoMode = $this->isDemoMode($tgUser);
        $amnesia = $tgUser->isNew() || $isDemoMode;
        $greeting = $amnesia ? 'Начинаем игру...' : 'Продолжаем игру...';

        $user = $tgUser->user();

        if ($isDemoMode) {
            // demo game
            $word = $this->languageService->getRandomStartingWord(
                $this->getLanguage($user),
                $tgUser->lastWord(),
                $user
            );

            $turn = PseudoTurn::new($word);

            return [
                $greeting,
                ...$this->turnsToParts(null, $turn)
            ];
        }

        // normal game
        $game = $this->gameService->getOrCreateNewGameFor($user);

        return [
            $greeting,
            ...$this->turnsToParts(
                $game->beforeLastTurn(),
                $game->lastTurn()
            )
        ];
    }

    /**
     * @return string[]
     */
    private function newGame(TelegramUser $tgUser, string $message): array
    {
        $isDemoMode = $this->isDemoMode($tgUser);
        $user = $tgUser->user();

        if ($isDemoMode) {
            // new demo game
            $word = $this->languageService->getRandomStartingWord(
                $this->getLanguage($user),
                $tgUser->lastWord(),
                $user
            );

            return $this->turnsToParts(
                null,
                PseudoTurn::new($word),
                $message
            );
        }

        // new normal game
        $newGame = $this->gameService->createNewGameFor($user);

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
        ?TurnInterface $question,
        ?TurnInterface $answer,
        ?string $noQuestionMessage = null
    ): array
    {
        if ($answer === null) {
            return [
                'Мне нечего сказать. 😥 Начинайте вы.'
            ];
        }

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

        if ($question === null) {
            return array_filter([
                $noQuestionMessage,
                $answerWordStr,
                ...$commands
            ]);
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

    private function turnStr(TurnInterface $turn): string
    {
        return '<b>' . mb_strtoupper($turn->word()->word) . '</b>';
    }

    private function getLanguage(User $user): Language
    {
        return $this->languageService->getCurrentLanguageFor($user);
    }

    private function isDemoMode(TelegramUser $tgUser): bool
    {
        return false;// $tgUser->isChat();
    }

    private function buildTelegramMessage(int $chatId, string $text): array
    {
        return [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            'text' => $text,
        ];
    }
}
