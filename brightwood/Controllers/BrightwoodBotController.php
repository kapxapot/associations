<?php

namespace Brightwood\Controllers;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\TelegramUserService;
use Brightwood\Collections\MessageCollection;
use Brightwood\External\TelegramTransport;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Stories\Story;
use Brightwood\Models\StoryStatus;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Collections\Basic\ArrayCollection;
use Plasticode\Controllers\Controller;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Util\Cases;
use Plasticode\Util\Strings;
use Plasticode\Util\Text;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

class BrightwoodBotController extends Controller
{
    private const STORY_SELECTION_COMMAND = '📚 Выбрать историю';

    private StoryRepositoryInterface $storyRepository;
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private TelegramUserService $telegramUserService;

    private TelegramTransport $telegram;

    private StoryParser $parser;

    // temp default
    private int $defaultStoryId = 1;

    // actions
    private string $masAction = '👦 Мальчик';
    private string $femAction = '👧 Девочка';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->storyStatusRepository = $container->storyStatusRepository;
        $this->storyRepository = $container->storyRepository;
        $this->telegramUserRepository = $container->telegramUserRepository;

        $this->telegramUserService = $container->telegramUserService;

        $this->telegram = $container->brightwoodTelegramTransport;

        $this->parser = new StoryParser();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $logEnabled = $this->getSettings('telegram.brightwood_bot_log', false) === true;

        $data = $request->getParsedBody();

        if (!empty($data) && $logEnabled) {
            $this->logger->info('Got BRIGHTWOOD request', $data);
        }

        $message = $data['message'] ?? null;

        $answers = $message
            ? $this->processIncomingMessage($message, $response)
            : null;

        if ($answers->any()) {
            foreach ($answers as $answer) {
                if ($logEnabled) {
                    $this->logger->info('Trying to send message', $answer);
                }

                $result = $this->telegram->sendMessage($answer);

                if ($logEnabled) {
                    $this->logger->info('Send message result: ' . $result);
                }
            }

            return $response;
        }

        throw new BadRequestException();
    }

    private function processIncomingMessage(array $message) : ArrayCollection
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

    private function getTelegramUser(array $data) : TelegramUser
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
    ) : ArrayCollection
    {
        try {
            return $this->getAnswersFromText($tgUser, $chatId, $text);
        } catch (\Exception $ex) {
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
    private function exceptionTrace(\Exception $ex) : array
    {
        $lines = [];

        foreach ($ex->getTrace() as $trace) {
            $lines[] = $trace['file'] . ' (' . $trace['line'] . '), ' . $trace['class'] . $trace['type'] . $trace['function'];
        }

        return $lines;
    }

    /**
     * @throws \Exception
     */
    private function getAnswersFromText(
        TelegramUser $tgUser,
        string $chatId,
        string $text
    ) : ArrayCollection
    {
        return ArrayCollection::from(
            $this
                ->getAnswers($tgUser, $text)
                ->map(
                    fn (MessageInterface $m)
                    => $this->toTelegramMessage($tgUser, $chatId, $m)
                )
        );
    }

    private function toTelegramMessage(
        TelegramUser $tgUser,
        string $chatId,
        MessageInterface $message
    ) : array
    {
        $message = $this->parseMessage($tgUser, $message);

        $actions = $message->actions();

        if (empty($actions)) {
            $actions = ['⏳'];
        }

        if (count($actions) == 1 && $actions[0] == Story::RESTART_ACTION) {
            $actions[] = self::STORY_SELECTION_COMMAND;
        }

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

    private function buildTelegramMessage(string $chatId, string $text) : array
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
    ) : MessageInterface
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

    private function messageToText(MessageInterface $message) : string
    {
        return Text::sparseJoin($message->lines());
    }

    private function getAnswers(TelegramUser $tgUser, string $text) : MessageCollection
    {
        // start command
        if (Strings::startsWith($text, '/start')) {
            return $this->startCommand($tgUser);
        }

        // check gender
        if (!$tgUser->hasGender()) {
            return $this->readGender($tgUser, $text);
        }

        // try executing story-specific commands
        if (Strings::startsWith($text, '/')) {
            $executionResults = $this->executeStoryCommand($tgUser, $text);

            if ($executionResults->any()) {
                return $executionResults->concat(
                    $this->currentStatusMessages($tgUser)
                );
            }
        }

        if (self::STORY_SELECTION_COMMAND == $text) {
            return MessageCollection::collect(
                $this->storySelection()
            );
        }

        // /story command
        if (preg_match("#^/story(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = $matches[1];

            $story = $this->storyRepository->get($storyId);

            if ($story) {
                return $this->switchToStory($tgUser, $story);
            }

            return $this
                ->currentStatusMessages($tgUser)
                ->prepend(
                    new TextMessage('История с id = ' . $storyId . ' не найдена.')
                );
        }

        // default - next step
        return $this->nextStep($tgUser, $text);
    }

    private function executeStoryCommand(
        TelegramUser $tgUser,
        string $command
    ) : MessageCollection
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            return null;
        }

        $story = $this->storyRepository->get($status->storyId);

        Assert::notNull($story);

        return $story->executeCommand($command);
    }

    private function startCommand(TelegramUser $tgUser) : MessageCollection
    {
        $status = $this->getStatus($tgUser);
        $isReader = !is_null($status);

        $greeting = $isReader ? 'С возвращением' : 'Добро пожаловать';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        $greetingMessage = new TextMessage($greeting);

        if (!$tgUser->hasGender()) {
            return MessageCollection::collect(
                $greetingMessage,
                $this->askGender()
            );
        }

        return $this
            ->startOrContinueStory($tgUser)
            ->prepend($greetingMessage);
    }

    private function readGender(TelegramUser $tgUser, string $text) : MessageCollection
    {
        /** @var integer|null */
        $gender = null;

        switch ($text) {
            case $this->masAction:
                $gender = Cases::MAS;
                break;

            case $this->femAction:
                $gender = Cases::FEM;
                break;
        }

        $genderIsOk = ($gender !== null);

        if (!$genderIsOk) {
            return MessageCollection::collect(
                new TextMessage('Вы написали что-то не то. 🤔'),
                $this->askGender()
            );
        }

        $tgUser->genderId = $gender;
        $this->telegramUserRepository->save($tgUser);

        return $this
            ->startOrContinueStory($tgUser)
            ->prepend(
                new TextMessage(
                    'Спасибо, {уважаемый 👦|уважаемая 👧}, ' .
                    'ваш пол сохранен и теперь будет учитываться. 👌'
                )
            );
    }

    private function askGender() : MessageInterface
    {
        return new Message(
            [
                'Для корректного текста историй, пожалуйста, укажите ваш <b>пол</b>:'
            ],
            [
                $this->masAction,
                $this->femAction
            ]
        );
    }

    /**
     * Starts the default story or continues the current one.
     */
    private function startOrContinueStory(TelegramUser $tgUser) : MessageCollection
    {
        $status = $this->getStatus($tgUser);

        if ($status) {
            return $this->continueStory($status);
        }

        return $this->startStory(
            $tgUser,
            $this->defaultStoryId
        );
    }

    private function continueStory(StoryStatus $status) : MessageCollection
    {
        return
            MessageCollection::collect(
                new TextMessage('Итак, продолжим...')
            )
            ->concat(
                $this->statusToMessages($status)
            );
    }

    private function startStory(TelegramUser $tgUser, int $storyId) : MessageCollection
    {
        $story = $this->storyRepository->get($storyId);

        $message = $story->start($tgUser);

        $this->storyStatusRepository->store(
            [
                'telegram_user_id' => $tgUser->getId(),
                'story_id' => $story->id(),
                'step_id' => $message->nodeId(),
                'json_data' => json_encode($message->data())
            ]
        );

        return MessageCollection::collect(
            new TextMessage('Итак, начнем...'),
            $message
        );
    }

    public function storySelection() : MessageInterface
    {
        $actions = [Story::RESTART_ACTION];

        $stories = $this->storyRepository->getAllPublished();

        if ($stories->isEmpty()) {
            return new Message(
                [
                    'Историй нет. Как вы вообще сюда попали?'
                ],
                $actions
            );
        }

        $storyLines = $stories->scalarize(
            fn (Story $s) => '/story_' . $s->id() . ' ' . $s->name()
        );

        return new Message(
            $storyLines->toArray(),
            $actions
        );
    }

    private function nextStep(TelegramUser $tgUser, string $text) : MessageCollection
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);

        Assert::notNull($node);

        $data = $story->makeData($tgUser, $status->data());
        $sequence = $story->go($tgUser, $node, $text, $data);

        if ($sequence) {
            $status->stepId = $sequence->nodeId();
            $status->jsonData = json_encode($sequence->data());

            $this->storyStatusRepository->save($status);

            return $sequence->messages();
        }

        return $this
            ->currentStatusMessages($tgUser)
            ->prepend(
                new TextMessage('Что-что? Повторите-ка... 🧐')
            );
    }

    private function switchToStory(TelegramUser $tgUser, Story $story) : MessageCollection
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        $sequence = $story->start($tgUser);

        $status->storyId = $story->id();
        $status->stepId = $sequence->nodeId();
        $status->jsonData = json_encode($sequence->data());

        $this->storyStatusRepository->save($status);

        return $sequence->messages();
    }

    private function currentStatusMessages(TelegramUser $tgUser) : MessageCollection
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        return $this->statusToMessages($status);
    }

    private function statusToMessages(StoryStatus $status) : MessageCollection
    {
        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);
        $data = $story->makeData($status->telegramUser(), $status->data());

        return $story
            ->renderNode($node, $data)
            ->messages();
    }

    private function getStatus(TelegramUser $tgUser) : ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($tgUser);
    }
}
