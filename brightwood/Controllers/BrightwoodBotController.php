<?php

namespace Brightwood\Controllers;

use App\Models\TelegramUser;
use App\Services\TelegramUserService;
use Brightwood\Answers\Answerer;
use Brightwood\External\TelegramTransport;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Stories\Story;
use Brightwood\Parsing\StoryParser;
use Exception;
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Text;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class BrightwoodBotController
{
    private const LOG_DISABLED = 0;
    private const LOG_BRIEF = 1;
    private const LOG_FULL = 2;

    private const TROUBLESHOOT_COMMAND = 'Бот сломался! Почините!';

    private SettingsProviderInterface $settingsProvider;
    private LoggerInterface $logger;

    private TelegramUserService $telegramUserService;
    private TelegramTransport $telegram;
    private Answerer $answerer;
    private StoryParser $parser;

    public function __construct(ContainerInterface $container)
    {
        $this->settingsProvider = $container->get(SettingsProviderInterface::class);
        $this->logger = $container->get(LoggerInterface::class);

        $this->telegramUserService = $container->get(TelegramUserService::class);
        $this->telegram = $container->get(TelegramTransport::class);
        $this->answerer = $container->get(Answerer::class);

        $this->parser = new StoryParser();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $logLevel = $this->settingsProvider->get(
            'telegram.brightwood_bot_log_level',
            self::LOG_DISABLED
        );

        $data = $request->getParsedBody();

        if (!empty($data) && $logLevel >= self::LOG_BRIEF) {
            $this->logger->info('Got BRIGHTWOOD request', $data);
        }

        $message = $data['message'] ?? null;

        $answers = $message
            ? $this->processIncomingMessage($message)
            : null;

        if ($answers->any()) {
            foreach ($answers as $answer) {
                if ($logLevel >= self::LOG_FULL) {
                    $this->logger->info('Trying to send message', $answer);
                }

                $result = $this->telegram->sendMessage($answer);

                if ($logLevel >= self::LOG_FULL) {
                    $this->logger->info('Send message result: ' . $result);
                }
            }
        }

        return $response;
    }

    private function processIncomingMessage(array $message): ArrayCollection
    {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? null);

        if (strlen($text) == 0) {
            $answer = $this->buildTelegramMessage(
                $chatId,
                '🧾 Я понимаю только сообщения с текстом.'
            );

            return ArrayCollection::collect($answer);
        }

        $from = $message['from'];
        $tgUser = $this->getTelegramUser($from);

        return $this->tryGetAnswersFromText($tgUser, $chatId, $text);
    }

    private function getTelegramUser(array $data): TelegramUser
    {
        $tgUser = $this
            ->telegramUserService
            ->getOrCreateTelegramUser($data);

        Assert::true($tgUser->isValid());

        return $tgUser;
    }

    private function tryGetAnswersFromText(
        TelegramUser $tgUser,
        string $chatId,
        string $text
    ): ArrayCollection
    {
        try {
            return $this->getAnswersFromText($tgUser, $chatId, $text);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());

            $this->logger->info(
                Text::join(
                    $this->exceptionTrace($ex)
                )
            );
        }

        $answer = $this->buildTelegramMessage(
            $chatId,
            'Что-то пошло не так. 😐'
        );

        return ArrayCollection::collect($answer);
    }

    /**
     * @return string[]
     */
    private function exceptionTrace(Exception $ex): array
    {
        $lines = [];

        foreach ($ex->getTrace() as $trace) {
            $lines[] = ($trace['file'] ?? '') . ' (' . ($trace['line'] ?? '') . '), ' . ($trace['class'] ?? '') . ($trace['type'] ?? '') . $trace['function'];
        }

        return $lines;
    }

    /**
     * @throws Exception
     */
    private function getAnswersFromText(
        TelegramUser $tgUser,
        string $chatId,
        string $text
    ): ArrayCollection
    {
        $sequence = $this->answerer->getAnswers($tgUser, $text);

        Assert::true(
            $sequence->hasText(),
            'Answers sequence must contain text.'
        );

        $defaultActions = $sequence->actions();

        if (empty($defaultActions)) {
            $defaultActions = $sequence->isFinalized()
                ? [Story::RESTART_COMMAND, Story::STORY_SELECTION_COMMAND]
                : [self::TROUBLESHOOT_COMMAND];
        }

        return ArrayCollection::from(
            $sequence
                ->messages()
                ->map(
                    fn (MessageInterface $m) =>
                        $this->toTelegramMessage($tgUser, $chatId, $m, $defaultActions)
                )
        );
    }

    /**
     * @param string[] $defaultActions
     */
    private function toTelegramMessage(
        TelegramUser $tgUser,
        string $chatId,
        MessageInterface $message,
        array $defaultActions
    ): array
    {
        $message = $this->parseMessage($tgUser, $message);
        $actions = $message->actions();

        if (empty($actions)) {
            $actions = $defaultActions;
        }

        Assert::notEmpty(
            $actions,
            'No messages without actions should be sent.'
        );

        $answer = $this->buildTelegramMessage(
            $chatId,
            $this->messageToText($message)
        );

        $answer['reply_markup'] = [
            'keyboard' => [$actions],
            'resize_keyboard' => true
        ];

        return $answer;
    }

    private function buildTelegramMessage(string $chatId, string $text): array
    {
        return [
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            'text' => $text
        ];
    }

    private function parseMessage(
        TelegramUser $tgUser,
        MessageInterface $message
    ): MessageInterface
    {
        $lines = array_map(
            fn (string $line) => $this->parser->parse($tgUser, $line, $message->data()),
            $message->lines()
        );

        $actions = array_map(
            fn (string $action) => $this->parser->parse($tgUser, $action, $message->data()),
            $message->actions()
        );

        return new Message($lines, $actions);
    }

    private function messageToText(MessageInterface $message): string
    {
        return Text::sparseJoin($message->lines());
    }
}
