<?php

namespace Brightwood\Controllers;

use App\External\Interfaces\TelegramTransportInterface;
use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\TelegramUserService;
use Brightwood\Answers\Answerer;
use Brightwood\Answers\AnswererFactory;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Models\BotCommand;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Parsing\StoryParser;
use Exception;
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Debug;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class BrightwoodBotController
{
    private const LOG_DISABLED = 0;
    private const LOG_BRIEF = 1;
    private const LOG_FULL = 2;

    private SettingsProviderInterface $settingsProvider;
    private LoggerInterface $logger;

    private TelegramUserRepositoryInterface $telegramUserRepository;
    private TelegramUserService $telegramUserService;
    private TelegramTransportInterface $telegram;
    private AnswererFactory $answererFactory;
    private StoryParser $parser;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger,
        TelegramUserRepositoryInterface $telegramUserRepository,
        TelegramUserService $telegramUserService,
        TelegramTransportFactory $telegramFactory,
        AnswererFactory $answererFactory,
        StoryParser $parser
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;

        $this->telegramUserRepository = $telegramUserRepository;
        $this->telegramUserService = $telegramUserService;
        $this->telegram = ($telegramFactory)();
        $this->answererFactory = $answererFactory;

        $this->parser = $parser;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $logLevel = $this->settingsProvider->get(
            'brightwood.log_level',
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

        if ($answers && $answers->any()) {
            /** @var array */
            foreach ($answers as $answer) {
                if ($logLevel >= self::LOG_FULL) {
                    $this->logger->info('Trying to send message', $answer);
                }

                try {
                    $result = $this->telegram->sendMessage($answer);
                } catch (Exception $ex) {
                    $result = $ex->getMessage();
                }

                if ($logLevel >= self::LOG_FULL) {
                    $this->logger->info("Send message result: {$result}");
                }
            }
        }

        return $response;
    }

    private function processIncomingMessage(array $message): ArrayCollection
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;
        $document = $message['document'] ?? null;

        $from = $message['from'];
        $tgUser = $this->getTelegramUser($from);

        return $this->tryGetAnswers($tgUser, $chatId, $text, $document);
    }

    private function getTelegramUser(array $data): TelegramUser
    {
        $tgUser = $this
            ->telegramUserService
            ->getOrCreateTelegramUser($data);

        Assert::true($tgUser->isValid());

        return $tgUser;
    }

    private function tryGetAnswers(
        TelegramUser $tgUser,
        string $chatId,
        ?string $text,
        ?array $document
    ): ArrayCollection
    {
        try {
            return $this->getAnswers($tgUser, $chatId, $text, $document);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());

            $this->logger->info(
                Text::join(
                    Debug::exceptionTrace($ex)
                )
            );
        }

        return ArrayCollection::collect(
            $this->buildTelegramMessage(
                $chatId,
                $this->parse(
                    $tgUser,
                    '[[Something went wrong.]] 😐'
                )
            )
        );
    }

    /**
     * @throws Exception
     */
    private function getAnswers(
        TelegramUser $tgUser,
        string $chatId,
        ?string $text,
        ?array $document
    ): ArrayCollection
    {
        $answerer = ($this->answererFactory)($tgUser);
        $sequence = $answerer->getAnswers($text, $document);

        $this->updateTelegramUser($tgUser, $sequence->stage());

        if ($sequence->isFinalized()) {
            if (!$sequence->hasText()) {
                $sequence->addText('[[The end]]');
            }
        } else {
            Assert::true(
                $sequence->hasText(),
                'Answers sequence must contain text.'
            );
        }

        $defaultActions = $sequence->actions();

        if (empty($defaultActions)) {
            $defaultActions = $sequence->isFinalized()
                ? [BotCommand::RESTART, BotCommand::STORY_SELECTION]
                : [BotCommand::TROUBLESHOOT];
        }

        return ArrayCollection::from(
            $sequence
                ->messages()
                ->map(
                    fn (MessageInterface $message) =>
                        $this->toTelegramMessage(
                            $tgUser,
                            $chatId,
                            $message,
                            $defaultActions,
                            $sequence->vars()
                        )
                )
        );
    }

    private function updateTelegramUser(TelegramUser $tgUser, ?string $stage): void
    {
        $key = Answerer::BRIGHTWOOD_STAGE;

        if ($stage) {
            $tgUser->setMetaValue($key, $stage);
            $tgUser->setDirty(true);
        } elseif ($tgUser->getMetaValue($key)) {
            $tgUser->deleteMetaValue($key);
            $tgUser->setDirty(true);
        }

        if ($tgUser->isDirty()) {
            $this->telegramUserRepository->save($tgUser);
        }
    }

    /**
     * @param string[] $defaultActions The actions that are used if the message doesn't have its own actions.
     */
    private function toTelegramMessage(
        TelegramUser $tgUser,
        string $chatId,
        MessageInterface $message,
        array $defaultActions,
        array $vars
    ): array
    {
        if (!$message->hasActions()) {
            $message->appendActions(...$defaultActions);
        }

        $message = $this->parseMessage($tgUser, $message, $vars);
        $actions = $message->actions();

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
            'resize_keyboard' => true,
        ];

        return $answer;
    }

    private function buildTelegramMessage(string $chatId, string $text): array
    {
        return [
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            'text' => $text,
        ];
    }

    private function parseMessage(
        TelegramUser $tgUser,
        MessageInterface $message,
        array $vars
    ): MessageInterface
    {
        $combinedVars = array_merge(
            $message->data() ? $message->data()->toArray() : [],
            $vars
        );

        $parse = fn (string $text) => $this->parse($tgUser, $text, $combinedVars);

        $lines = array_map($parse, $message->lines());
        $actions = array_map($parse, $message->actions());

        return new Message($lines, $actions);
    }

    private function messageToText(MessageInterface $message): string
    {
        return Text::sparseJoin($message->lines());
    }

    private function parse(TelegramUser $tgUser, string $text, ?array $vars = null): string
    {
        return $this->parser->parse($tgUser, $text, $vars);
    }
}
