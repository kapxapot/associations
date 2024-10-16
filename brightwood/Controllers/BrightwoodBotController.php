<?php

namespace Brightwood\Controllers;

use App\External\Interfaces\TelegramTransportInterface;
use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\TelegramUserService;
use Brightwood\Answers\Action;
use Brightwood\Answers\AnswererFactory;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\MetaKey;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\TelegramUserService as BrightwoodTelegramUserService;
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

    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;
    private TelegramUserService $telegramUserService;
    private BrightwoodTelegramUserService $brightwoodTelegramUserService;

    private TelegramTransportInterface $telegram;
    private AnswererFactory $answererFactory;
    private StoryParser $parser;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger,
        StoryStatusRepositoryInterface $storyStatusRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        TelegramUserService $telegramUserService,
        BrightwoodTelegramUserService $brightwoodTelegramUserService,
        TelegramTransportFactory $telegramFactory,
        AnswererFactory $answererFactory,
        StoryParser $parser
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;

        $this->storyStatusRepository = $storyStatusRepository;
        $this->telegramUserRepository = $telegramUserRepository;
        $this->telegramUserService = $telegramUserService;
        $this->brightwoodTelegramUserService = $brightwoodTelegramUserService;

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
                    if (isset($answer['photo'])) {
                        $result = $this->telegram->sendPhoto($answer);
                    } else {
                        $result = $this->telegram->sendMessage($answer);
                    }
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
        $tgLangCode = $from['language_code'];

        $tgUser = $this->getTelegramUser($from);

        return $this->tryGetAnswers($tgUser, $tgLangCode, $chatId, $text, $document);
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
        string $tgLangCode,
        string $chatId,
        ?string $text,
        ?array $document
    ): ArrayCollection
    {
        try {
            return $this->getAnswers($tgUser, $tgLangCode, $chatId, $text, $document);
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
                    $tgLangCode,
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
        string $tgLangCode,
        string $chatId,
        ?string $text,
        ?array $document
    ): ArrayCollection
    {
        $answerer = ($this->answererFactory)($tgUser, $tgLangCode);
        $sequence = $answerer->getAnswers($text, $document);

        $this->updateTelegramUser($tgUser, $sequence->meta());

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
            if ($sequence->isFinalized() || $sequence->isStuck()) {
                $defaultActions = [];

                $status = $this->storyStatusRepository->getByTelegramUser($tgUser);

                if ($status) {
                    $defaultActions[] = Action::RESTART;
                    $defaultActions[] = Action::SHOW_STORY;
                }

                $defaultActions[] = [Action::STORY_SELECTION]; // separate line!
            } else {
                $defaultActions = [Action::TROUBLESHOOT];
            }
        }

        if ($this->brightwoodTelegramUserService->isAdmin($tgUser)) {
            if ($sequence->isFinalized()) {
                $sequence->addText('[FINALIZED]');
            }

            if ($sequence->isStuck()) {
                $sequence->addText('[STUCK]');
            }
        }

        $messages = $sequence->splitImageMessages();

        return ArrayCollection::from(
            $messages->map(
                fn (MessageInterface $message) => $this->toTelegramMessage(
                    $tgUser,
                    $tgLangCode,
                    $chatId,
                    $message,
                    $defaultActions,
                    $sequence->vars()
                )
            )
        );
    }

    private function updateTelegramUser(TelegramUser $tgUser, array $meta): void
    {
        foreach (MetaKey::all() as $key) {
            $meta[$key] ??= null;
        }

        $tgUser->withMeta($meta);

        if ($tgUser->isDirty()) {
            $this->telegramUserRepository->save($tgUser);
        }
    }

    /**
     * @param (string|string[])[] $defaultActions The actions that are used if the message doesn't have its own actions.
     */
    private function toTelegramMessage(
        TelegramUser $tgUser,
        string $tgLangCode,
        string $chatId,
        MessageInterface $message,
        array $defaultActions,
        array $vars
    ): array
    {
        if (!$message->hasActions()) {
            $message->appendActions(...$defaultActions);
        }

        $parsedMessage = $this->parseMessage($tgUser, $tgLangCode, $message, $vars);
        $actions = $parsedMessage->actions();

        Assert::notEmpty(
            $actions,
            'No messages without actions should be sent.'
        );

        $answer = $this->buildTelegramMessage(
            $chatId,
            $this->messageToText($parsedMessage),
            $message->image()
        );

        $answer['reply_markup'] = [
            'keyboard' => $this->groupActions($actions),
            'resize_keyboard' => true,
        ];

        return $answer;
    }

    /**
     * Groups actions to string arrays.
     *
     * @param (string|string[])[] $actions
     * @return string[][]
     */
    private function groupActions(array $actions): array
    {
        $result = [];
        $accumulator = [];

        $flush = function() use (&$result, &$accumulator) {
            if (empty($accumulator)) {
                return;
            }

            $result[] = $accumulator;
            $accumulator = [];
        };

        foreach ($actions as $action) {
            if (is_array($action)) {
                $flush();
                $result[] = $action;
                continue;
            }

            $accumulator[] = $action;
        }

        $flush();

        return $result;
    }

    private function buildTelegramMessage(
        string $chatId,
        ?string $text = null,
        ?string $image = null
    ): array
    {
        $result = [
            'chat_id' => $chatId,
            'parse_mode' => 'html',
        ];

        if (strlen($image) > 0) {
            $result['photo'] = $image;
            $result['caption'] = $text;
        } else {
            $result['text'] = $text;
        }

        return $result;
    }

    private function parseMessage(
        TelegramUser $tgUser,
        string $tgLangCode,
        MessageInterface $message,
        array $vars
    ): MessageInterface
    {
        $combinedVars = array_merge(
            $message->data() ? $message->data()->toArray() : [],
            $vars
        );

        $parse = fn (string $text) => $this->parse($tgUser, $tgLangCode, $text, $combinedVars);

        $lines = array_map($parse, $message->lines());

        $actions = array_map(
            fn ($action) => is_array($action)
                ? array_map($parse, $action)
                : $parse($action),
            $message->actions()
        );

        return new Message($lines, $actions);
    }

    private function messageToText(MessageInterface $message): string
    {
        return Text::sparseJoin($message->lines());
    }

    private function parse(
        TelegramUser $tgUser,
        string $tgLangCode,
        string $text,
        ?array $vars = null
    ): string
    {
        return $this->parser->parse($tgUser, $text, $vars, $tgLangCode);
    }
}
