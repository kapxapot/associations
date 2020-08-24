<?php

namespace App\Controllers\Brightwood;

use App\Controllers\Controller;
use App\Models\Brightwood\Links\ActionLink;
use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
use App\Models\Brightwood\Stories\Story;
use App\Models\Brightwood\StoryMessage;
use App\Models\Brightwood\StoryStatus;
use App\Models\TelegramUser;
use App\Repositories\Brightwood\Interfaces\StoryRepositoryInterface;
use App\Repositories\Brightwood\Interfaces\StoryStatusRepositoryInterface;
use App\Services\TelegramUserService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\Util\Text;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

class BrightwoodBotController extends Controller
{
    private StoryRepositoryInterface $storyRepository;
    private StoryStatusRepositoryInterface $storyStatusRepository;

    private TelegramUserService $telegramUserService;

    // temp default
    private int $defaultStoryId = 2;
    private string $restartAction = 'Начать заново';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->storyStatusRepository = $container->storyStatusRepository;
        $this->storyRepository = $container->storyRepository;

        $this->telegramUserService = $container->telegramUserService;
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
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
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
        $actions = $message->actions();

        if (empty($actions)) {
            $actions = ['Бот сломался! Почините!'];
        }

        $result['text'] = Text::sparseJoin(
            $message->lines()
        );

        $result['reply_markup'] = [
            'keyboard' => [$actions],
            'resize_keyboard' => true
        ];

        return $result;
    }

    private function getAnswer(TelegramUser $tgUser, string $text) : StoryMessage
    {
        if (strpos($text, '/start') === 0) {
            return $this->startCommand($tgUser);
        }

        if (preg_match("#^/story\s+(\d+)$#i", $text, $matches)) {
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

        return $this->nextStep($tgUser, $text);
    }

    private function startCommand(TelegramUser $tgUser) : StoryMessage
    {
        $isReader = $tgUser->isReader();

        $greeting = $isReader ? 'С возвращением' : 'Добро пожаловать';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        $status = $tgUser->storyStatus();

        if ($status) {
            $message = $this->statusToMessage($status);
        } else {
            $story = $this->storyRepository->get($this->defaultStoryId);
            $node = $story->startNode();

            $message = $this->checkForFinish(
                $story,
                $node->getMessage()
            );

            $status = $this->storyStatusRepository->store(
                [
                    'telegram_user_id' => $tgUser->getId(),
                    'story_id' => $story->id(),
                    'step_id' => $message->nodeId(),
                ]
            );
        }

        return $message->prependLines(
            $greeting,
            $isReader ? 'Итак, продолжим...' : 'Итак, начнем...'
        );
    }

    private function nextStep(TelegramUser $tgUser, string $text) : StoryMessage
    {
        $status = $tgUser->storyStatus();

        Assert::notNull($status);

        $story = $this->storyRepository->get($status->storyId);

        $node = $story->getNode($status->stepId);

        /** @var StoryNode|null */
        $nextNode = null;

        if ($node instanceof ActionNode) {
            /** @var ActionLink */
            foreach ($node->links() as $link) {
                if ($link->action() !== $text) {
                    continue;
                }

                $nextNode = $story->getNode(
                    $link->nodeId()
                );

                if ($nextNode) {
                    break;
                }
            }
        } elseif ($node instanceof FinishNode) {
            if ($this->restartAction === $text) {
                $nextNode = $story->startNode();
            }
        } else {
            throw new \Exception('Incorrect node type: ' . get_class($node));
        }

        if ($nextNode) {
            $message = $nextNode->getMessage();

            $status->stepId = $message->nodeId();
            $this->storyStatusRepository->save($status);

            return $this->checkForFinish($story, $message);
        }

        return $this->currentStatusMessage(
            $tgUser,
            'Что-что? Повторите-ка... 🧐'
        );
    }

    private function switchToStory(TelegramUser $tgUser, Story $story) : StoryMessage
    {
        $node = $story->startNode();
        $message = $node->getMessage();

        $status = $tgUser->storyStatus();

        Assert::notNull($status);

        $status->storyId = $story->id();
        $status->stepId = $message->nodeId();

        $this->storyStatusRepository->save($status);

        return $this->checkForFinish($story, $message);
    }

    private function currentStatusMessage(
        TelegramUser $tgUser,
        string ...$prependLines
    ) : StoryMessage
    {
        $status = $tgUser->storyStatus();

        Assert::notNull($status);

        return $this
            ->statusToMessage($status)
            ->prependLines(...$prependLines);
    }

    private function statusToMessage(StoryStatus $status) : StoryMessage
    {
        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);

        return $this->checkForFinish(
            $story,
            $node->getMessage()
        );
    }

    private function checkForFinish(Story $story, StoryMessage $message) : StoryMessage
    {
        $messageNode = $story->getNode($message->nodeId());

        return $messageNode->isFinish()
            ? $message->withActions($this->restartAction)
            : $message;
    }
}
