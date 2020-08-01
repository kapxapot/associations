<?php

namespace App\Controllers;

use App\Exceptions\DuplicateWordException;
use App\Models\Association;
use App\Models\TelegramUser;
use App\Models\Turn;
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

            $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

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
                //$this->logger->error($vEx->firstError(), $vEx->errors());
                $error = $vEx->firstError();
            } catch (DuplicateWordException $dwEx) {
                $error = 'Слово <b>' . mb_strtoupper($dwEx->word) . '</b> уже использовано в игре.';
            }

            if ($error) {
                return '❌ ' . $error;
            }

            if ($turns->count() > 1) {
                // continuing current game
                /** @var Turn */
                $question = $turns->first();
                /** @var Turn */
                $answer = $turns->skip(1)->first();
            } else {
                // no answer, starting new game
                $newGame = $this->gameService->createGameFor($user);

                $question = null;
                $answer = $newGame->lastTurn();
            }
        }

        if (is_null($answer)) {
            $parts[] = 'Мне нечего сказать. 😥 Начинайте вы.';
        } else {
            Assert::true($answer->isAiTurn());

            $answerWord = mb_strtoupper($answer->word()->word);

            if (is_null($question)) {
                $parts[] = 'У меня нет ассоциаций. 😥 Начинаем заново!';
                $parts[] = '<b>' . $answerWord . '</b>';
            } else {
                $questionWord = mb_strtoupper($question->word()->word);

                $association = $answer->association();

                $sign = $association
                    ? $association->sign()
                    : Association::DEFAULT_SIGN;

                $parts[] = '<b>' . $questionWord . '</b> ' . $sign . ' <b>' . $answerWord . '</b>';
            }
        }

        return implode(PHP_EOL . PHP_EOL, $parts);
    }
}
