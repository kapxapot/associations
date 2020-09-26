<?php

namespace Brightwood\Controllers;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Services\TelegramUserService;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Stories\Story;
use Brightwood\Models\StoryStatus;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
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

    private StoryParser $parser;

    // temp default
    private int $defaultStoryId = 1;

    // actions
    private string $masAction = 'Мальчик 👦';
    private string $femAction = 'Девочка 👧';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->storyStatusRepository = $container->storyStatusRepository;
        $this->storyRepository = $container->storyRepository;
        $this->telegramUserRepository = $container->telegramUserRepository;

        $this->telegramUserService = $container->telegramUserService;

        $this->parser = new StoryParser();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();

        if (!empty($data)) {
            $logEnabled = $this->getSettings('telegram.brightwood_bot_log', false);

            if ($logEnabled === true) {
                $this->logger->info('Got BRIGHTWOOD request', $data);
            }
        }

        $message = $data['message'] ?? null;

        $processed = $message
            ? $this->processIncomingMessage($message, $response)
            : null;

        if ($processed) {
            return Response::json($response, $processed);
        }

        throw new BadRequestException();
    }

    private function processIncomingMessage(array $message) : ?array
    {
        $result = [];

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        $tgUser = $this
            ->telegramUserService
            ->getOrCreateTelegramUser(
                $message['from']
            );

        Assert::true($tgUser->isValid());

        $result = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'parse_mode' => 'html',
        ];

        $text = trim($text);

        if (strlen($text) == 0) {
            $result['text'] = '🧾 Я понимаю только сообщения с текстом.';
        } else {
            $result = $this->tryParseText($result, $tgUser, $text);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function tryParseText(
        array $result,
        TelegramUser $tgUser,
        string $text
    ) : array
    {
        try {
            $result = $this->parseText($result, $tgUser, $text);
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());

            $lines = [];

            foreach ($ex->getTrace() as $trace) {
                $lines[] = $trace['file'] . ' (' . $trace['line'] . '), ' . $trace['class'] . $trace['type'] . $trace['function'];
            }

            $this->logger->info(Text::join($lines));

            $result['text'] = 'Что-то пошло не так. 😐';
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     * 
     * @throws \Exception
     */
    private function parseText(
        array $result,
        TelegramUser $tgUser,
        string $text
    ) : array
    {
        $message = $this->getAnswer($tgUser, $text);
        $message = $this->parseMessage($tgUser, $message);

        $actions = $message->actions();

        if (empty($actions)) {
            $actions = ['Бот сломался! Почините!'];
        }

        if (count($actions) == 1 && $actions[0] == Story::RESTART_ACTION) {
            $actions[] = self::STORY_SELECTION_COMMAND;
        }

        $result['text'] = $this->messageToText($message);

        $result['reply_markup'] = [
            'keyboard' => [$actions],
            'resize_keyboard' => true
        ];

        return $result;
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

    private function getAnswer(TelegramUser $tgUser, string $text) : MessageInterface
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
            $executionResult = $this->executeStoryCommand($tgUser, $text);

            if ($executionResult) {
                return $this->currentStatusMessage(
                    $tgUser,
                    ...$executionResult->lines()
                );
            }
        }

        if (self::STORY_SELECTION_COMMAND == $text) {
            return $this->storySelection();
        }

        // /story command
        if (preg_match("#^/story(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = $matches[1];

            $story = $this->storyRepository->get($storyId);

            if ($story) {
                return $this->switchToStory($tgUser, $story);
            }

            return $this->currentStatusMessage(
                $tgUser,
                'История с id = ' . $storyId . ' не найдена.'
            );
        }

        // default - next step
        return $this->nextStep($tgUser, $text);
    }

    private function executeStoryCommand(
        TelegramUser $tgUser,
        string $command
    ) : ?MessageInterface
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            return null;
        }

        $story = $this->storyRepository->get($status->storyId);

        Assert::notNull($story);

        return $story->tryExecuteCommand($command);
    }

    private function startCommand(TelegramUser $tgUser) : MessageInterface
    {
        $status = $this->getStatus($tgUser);
        $isReader = !is_null($status);

        $greeting = $isReader ? 'С возвращением' : 'Добро пожаловать';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        if (!$tgUser->hasGender()) {
            return $this
                ->askGender()
                ->prependLines($greeting);
        }

        return $this
            ->startOrContinueStory($tgUser)
            ->prependLines($greeting);
    }

    private function readGender(TelegramUser $tgUser, string $text) : MessageInterface
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
            return $this
                ->askGender()
                ->prependLines('Вы написали что-то не то. 🤔');
        }

        $tgUser->genderId = $gender;
        $this->telegramUserRepository->save($tgUser);

        return $this
            ->startOrContinueStory($tgUser)
            ->prependLines(
                'Спасибо, {уважаемый 👦|уважаемая 👧}, ваш пол сохранен и теперь будет учитываться. 👌'
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
    private function startOrContinueStory(TelegramUser $tgUser) : StoryMessage
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

    private function continueStory(StoryStatus $status) : StoryMessage
    {
        return $this
            ->statusToMessage($status)
            ->prependLines('Итак, продолжим...');
    }

    private function startStory(TelegramUser $tgUser, int $storyId) : StoryMessage
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

        return $message->prependLines('Итак, начнем...');
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

    private function nextStep(TelegramUser $tgUser, string $text) : StoryMessage
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);

        Assert::notNull($node);

        $data = $story->makeData($tgUser, $status->data());
        $message = $story->go($tgUser, $node, $text, $data);

        if ($message) {
            $status->stepId = $message->nodeId();
            $status->jsonData = json_encode($message->data());

            $this->storyStatusRepository->save($status);

            return $message;
        }

        return $this->currentStatusMessage(
            $tgUser,
            'Что-что? Повторите-ка... 🧐'
        );
    }

    private function switchToStory(TelegramUser $tgUser, Story $story) : StoryMessage
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        $message = $story->start($tgUser);

        $status->storyId = $story->id();
        $status->stepId = $message->nodeId();
        $status->jsonData = json_encode($message->data());

        $this->storyStatusRepository->save($status);

        return $message;
    }

    private function currentStatusMessage(
        TelegramUser $tgUser,
        string ...$prependLines
    ) : StoryMessage
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        return $this
            ->statusToMessage($status)
            ->prependLines('* * *')
            ->prependLines(...$prependLines);
    }

    private function statusToMessage(StoryStatus $status) : StoryMessage
    {
        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);
        $data = $story->makeData($status->telegramUser(), $status->data());

        return $story->renderNode($node, $data);
    }

    private function getStatus(TelegramUser $tgUser) : ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($tgUser);
    }
}
