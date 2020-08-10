<?php

namespace App\Controllers\Brightwood;

use App\Controllers\Controller;
use App\Models\Brightwood\Links\ActionLink;
use App\Models\Brightwood\Nodes\ActionNode;
use App\Models\Brightwood\Nodes\FinishNode;
use App\Models\Brightwood\Story;
use App\Models\Brightwood\StoryMessage;
use App\Models\Brightwood\StoryStatus;
use App\Models\TelegramUser;
use App\Repositories\Brightwood\Interfaces\StoryRepositoryInterface;
use App\Repositories\Brightwood\Interfaces\StoryStatusRepositoryInterface;
use App\Services\TelegramUserService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
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
    private int $storyId = 1;
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
            $this->logger->info('Got BRIGHTWOOD request', $data);
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
            $result['text'] = '🧾 Я понимаю только сообщения с текстом.';
        } else {
            try {
                $message = $this->getAnswer($tgUser, $text);

                $actions = empty($message->actions())
                    ? ['Бот сломался! Почините!']
                    : $message->actions();

                $result['text'] = implode(
                    PHP_EOL . PHP_EOL,
                    $message->lines()
                );

                $result['reply_markup'] = [
                    'keyboard' => [$actions],
                    'resize_keyboard' => true
                ];
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $result['text'] = 'Что-то пошло не так. 😐';
            }
        }

        return $result;
    }

    private function getAnswer(TelegramUser $tgUser, string $text) : StoryMessage
    {
        if (strpos($text, '/start') === 0) {
            return $this->startCommand($tgUser);
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
            $story = $this->storyRepository->get($this->storyId);
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

        return $this->statusToMessage($status)->prependLines(
            'Что-что? Повторите-ка... 🧐'
        );
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
