<?php

namespace App\Controllers;

use App\Exceptions\DuplicateWordException;
use App\Models\TelegramUser;
use App\Services\GameService;
use App\Services\TelegramUserService;
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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->gameService = $container->gameService;
        $this->telegramUserService = $container->telegramUserService;
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
            'parse_mode' => 'markdown',
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

        try {
            $answer = $this->getAnswer($tgUser, $text);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            $answer = 'Что-то пошло не так. ☹';
        }

        $result['text'] = $answer;

        return $result;
    }

    private function getAnswer(TelegramUser $tgUser, ?string $text) : string
    {
        if (strlen($text) == 0) {
            return '🧾 Я понимаю только сообщения с текстом.';
        }

        $parts = [];

        $user = $tgUser->user();
        $isNewUser = $tgUser->isNew();

        $game = $this->gameService->getOrCreateGameFor($tgUser->user());

        Assert::notNull($game);

        $answer = $game->lastTurn();
        $question = $game->beforeLastTurn();

        if (strpos($text, '/start') === 0) {
            $greeting = $isNewUser
                ? 'Добро пожаловать'
                : 'С возвращением';

            $greeting .= ', *' . $tgUser->privateName() . '*!';

            $parts[] = $greeting;
            $parts[] = $isNewUser
                ? 'Начинаем игру...'
                : 'Продолжаем игру...';
        } else {
            // word
            /** @var string|null */
            $error = null;

            try {
                $turns = $this->gameService->makeTurn($user, $game, $text);
            } catch (ValidationException $vEx) {
                $error = $this->translate($vEx->getMessage());
            } catch (DuplicateWordException $dwEx) {
                $error = 'Слово *' . $dwEx->word . '* уже использовано в игре.';
            }

            if ($error) {
                return '❌ ' . $error;
            }

            if ($turns->count() > 1) {
                // continuing current game
                [$question, $answer] = $turns->toArray();
            } else {
                // no answer, starting new game
                $newGame = $this->gameService->createGameFor($user);

                $question = null;
                $answer = $newGame->lastTurn();
            }
        }

        if ($answer) {
            Assert::true($answer->isAiTurn());

            if ($question) {
                $parts[] = 'На *' . $question->word()->word . '* я говорю: *' . $answer->word()->word . '*';
            } else {
                $parts[] = 'Я говорю новое слово: *' . $answer->word()->word . '*';
            }
        } else {
            $parts[] = 'Мне нечего сказать, начинайте вы.';
        }

        return implode(PHP_EOL . PHP_EOL, $parts);
    }
}
