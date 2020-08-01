<?php

namespace App\Controllers;

use App\Exceptions\DuplicateWordException;
use App\Models\Association;
use App\Models\TelegramUser;
use App\Models\Turn;
use App\Models\User;
use App\Models\Validation\AgeValidation;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\GameService;
use App\Services\TelegramUserService;
use App\Services\TurnService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Exceptions\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

class TelegramBotController extends Controller
{
    private UserRepositoryInterface $userRepository;

    private GameService $gameService;
    private TelegramUserService $telegramUserService;
    private TurnService $turnService;

    private AgeValidation $ageValidation;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->userRepository = $container->userRepository;

        $this->gameService = $container->gameService;
        $this->telegramUserService = $container->telegramUserService;
        $this->turnService = $container->turnService;

        $this->ageValidation = $container->ageValidation;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();

        if (!empty($data)) {
            $this->logger->info('Got request', $data);
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

    private function processMessage(array $message) : ?array
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
    private function getAnswer(TelegramUser $tgUser, string $text) : array
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

        return $this->sayWord($tgUser, $text);
    }

    /**
     * @return string[]
     */
    private function startCommand(TelegramUser $tgUser) : array
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
    private function readAge(TelegramUser $tgUser, string $text) : array
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
    private function askAge() : array
    {
        return [
            'Пожалуйста, укажите ваш возраст (цифрами):'
        ];
    }

    /**
     * @return string[]
     */
    private function skipCommand(TelegramUser $tgUser) : array
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
    private function sayWord(TelegramUser $tgUser, string $text) : array
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
    private function startGame(TelegramUser $tgUser) : array
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
    private function newGame(User $user, string $message) : array
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
    ) : array
    {
        if (is_null($answer)) {
            return [
                'Мне нечего сказать. 😥 Начинайте вы.'
            ];
        }

        Assert::true($answer->isAiTurn());

        $answerWord = $this->turnStr($answer);

        if (is_null($question)) {
            return array_filter(
                [
                    $noQuestionMessage,
                    $answerWord
                ]
            );
        }

        $questionWord = $this->turnStr($question);

        $association = $answer->association();

        $sign = $association
            ? $association->sign()
            : Association::DEFAULT_SIGN;

        $associationStr = $questionWord . ' ' . $sign . ' ' . $answerWord;

        return [
            $associationStr
        ];
    }

    private function turnStr(Turn $turn) : string
    {
        return '<b>' . $turn->word()->word . '</b>';
    }
}
