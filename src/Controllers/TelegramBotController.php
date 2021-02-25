<?php

namespace App\Controllers;

use App\Exceptions\DuplicateWordException;
use App\Models\Association;
use App\Models\TelegramUser;
use App\Models\Turn;
use App\Models\User;
use App\Models\Validation\AgeValidation;
use App\Parsing\DefinitionParser;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Semantics\Definition\DefinitionEntry;
use App\Services\GameService;
use App\Services\TelegramUserService;
use App\Services\TurnService;
use Exception;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class TelegramBotController
{
    private SettingsProviderInterface $settingsProvider;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    private UserRepositoryInterface $userRepository;

    private GameService $gameService;
    private TelegramUserService $telegramUserService;
    private TurnService $turnService;

    private AgeValidation $ageValidation;

    private DefinitionParser $definitionParser;

    private string $languageCode;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        GameService $gameService,
        TelegramUserService $telegramUserService,
        TurnService $turnService,
        AgeValidation $ageValidation,
        DefinitionParser $definitionParser
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;
        $this->translator = $translator;

        $this->userRepository = $userRepository;

        $this->gameService = $gameService;
        $this->telegramUserService = $telegramUserService;
        $this->turnService = $turnService;

        $this->ageValidation = $ageValidation;

        $this->definitionParser = $definitionParser;

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

        $processed = $message
            ? $this->processMessage($message, $response)
            : null;

        if ($processed) {
            return Response::json($response, $processed);
        }

        throw new BadRequestException();
    }

    private function processMessage(array $message): ?array
    {
        $result = [];

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        $tgUser = $this->telegramUserService->getOrCreateTelegramUser($message['from']);

        Assert::true($tgUser->isValid());

        $result = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
        ];

        $text = trim($text);

        if (strlen($text) == 0) {
            $answer = '🧾 Я понимаю только сообщения с текстом.';
        } else {
            try {
                $answerParts = $this->getAnswer($tgUser, $text);
                $answer = implode(PHP_EOL . PHP_EOL, $answerParts);
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $answer = 'Что-то пошло не так. 😐';
            }
        }

        $result['text'] = $answer;

        return $result;
    }

    /**
     * @return string[]
     */
    private function getAnswer(TelegramUser $tgUser, string $text): array
    {
        if (strpos($text, '/start') === 0) {
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
            return $this->whatCommand($tgUser);
        }

        return $this->sayWord($tgUser, $text);
    }

    /**
     * @return string[]
     */
    private function startCommand(TelegramUser $tgUser): array
    {
        $user = $tgUser->user();

        $greeting = $tgUser->isNew() ? 'Добро пожаловать' : 'С возвращением';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        if (!$user->hasAge()) {
            return [
                $greeting,
                ...$this->askAge()
            ];
        }

        return [
            $greeting,
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
    private function whatCommand(TelegramUser $tgUser): array
    {
        $user = $tgUser->user();
        $game = $user->currentGame();

        Assert::notNull($game);

        /** @var Turn $lastTurn */
        $lastTurn = $game->turns()->first();

        if ($lastTurn === null) {
            return ['Вы о чем?'];
        }

        $noDefinition = ['Определение отсутствует.'];

        $word = $lastTurn->word();
        $definition = $word->definition();

        if ($definition === null || !$definition->isValid()) {
            return $noDefinition;
        }

        $parsedDefinition = $this->definitionParser->parse($definition);

        if ($parsedDefinition === null) {
            return $noDefinition;
        }

        $defText = [];

        $defEntries = $parsedDefinition->entries();

        for ($index = 0; $index < $defEntries->count(); $index++) {
            /** @var DefinitionEntry $defEntry */
            $defEntry = $defEntries[$index];

            $defTitle = '<b>' . mb_strtoupper($word->word) . '</b>';

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
        } catch (DuplicateWordException $dwEx) {
            $word = mb_strtoupper($dwEx->word);

            return [
                '❌ Слово <b>' . $word . '</b> уже использовано в игре.'
            ];
        }

        if ($turns->count() > 1) {
            // continuing current game
            return $this->turnsToParts(
                $turns->first(),
                $turns->skip(1)->first()
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

        $definition = $answer->word()->definition();

        if ($definition && $definition->isValid()) {
            $parsedDefinition = $this->definitionParser->parse($definition);

            if ($parsedDefinition) {
                $commands[] = '/what Что такое "' . $answer->word()->word . '"?';
            }
        }

        // $commands[] = '/skip Другое слово';

        if (is_null($question)) {
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
