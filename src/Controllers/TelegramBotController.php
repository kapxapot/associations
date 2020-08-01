<?php

namespace App\Controllers;

use App\Exceptions\DuplicateWordException;
use App\Models\Association;
use App\Models\TelegramUser;
use App\Models\Turn;
use App\Models\User;
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
    private GameService $gameService;
    private TelegramUserService $telegramUserService;
    private TurnService $turnService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameService = $container->gameService;
        $this->telegramUserService = $container->telegramUserService;
        $this->turnService = $container->turnService;
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

        $messageId = $message['message_id'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        $tgUser = $this->telegramUserService->getOrCreateTelegramUser($message['from']);

        Assert::true($tgUser->isValid());

        $result = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            //'reply_to_message_id' => $messageId,
        ];

        // if ($text == '/keyboard') {
        //     $result['text'] = 'Hey...';
        //     $result['reply_markup'] = [
        //         'keyboard' => [['Пропустить']],
        //         'one_time_keyboard' => true,
        //         'resize_keyboard' => true
        //     ];

        //     return $result;
        // }

        $text = trim($text);

        if (strlen($text) == 0) {
            $answer = '🧾 Я понимаю только сообщения с текстом.';
        } else {
            try {
                $answerParts = $this->getAnswer($tgUser, $text);
                $answer = implode(PHP_EOL . PHP_EOL, $answerParts);
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $answer = 'Что-то пошло не так. ☹';
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
        $isNewUser = $tgUser->isNew();

        $game = $this->gameService->getOrCreateGameFor($user);

        Assert::notNull($game);

        $answer = $game->lastTurn();
        $question = $game->beforeLastTurn();

        $greeting = $isNewUser ? 'Добро пожаловать' : 'С возвращением';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        return [
            $greeting,
            $isNewUser ? 'Начинаем игру...' : 'Продолжаем игру...',
            ...$this->turnsToParts($question, $answer)
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
        Assert::true(
            $question || $noQuestionMessage
        );

        if (is_null($answer)) {
            return [
                'Мне нечего сказать. 😥 Начинайте вы.'
            ];
        }

        Assert::true($answer->isAiTurn());

        $answerWord = mb_strtoupper($answer->word()->word);

        if (is_null($question)) {
            return [
                $noQuestionMessage,
                '<b>' . $answerWord . '</b>'
            ];
        }

        $questionWord = mb_strtoupper($question->word()->word);

        $association = $answer->association();

        $sign = $association
            ? $association->sign()
            : Association::DEFAULT_SIGN;

        return [
            '<b>' . $questionWord . '</b> ' . $sign . ' <b>' . $answerWord . '</b>'
        ];
    }
}
