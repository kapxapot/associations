<?php

namespace Brightwood\Answers;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Semantics\Gender;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Strings;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * Returns story message sequence in answer to a text from a telegram user.
 *
 * Has some other side effects (and this is not good):
 * 
 * - Can change telegram users (set gender).
 * - Can create and change story statuses.
 */
class Answerer
{
    use LoggerAwareTrait;

    private int $defaultStoryId = 1;

    private string $masAction = '👦 Мальчик';
    private string $femAction = '👧 Девочка';

    private StoryRepositoryInterface $storyRepository;
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    public function __construct(
        StoryRepositoryInterface $storyRepository,
        StoryStatusRepositoryInterface $storyStatusRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        LoggerInterface $logger
    )
    {
        $this->storyRepository = $storyRepository;
        $this->storyStatusRepository = $storyStatusRepository;
        $this->telegramUserRepository = $telegramUserRepository;

        $this->withLogger($logger);
    }

    public function getAnswers(TelegramUser $tgUser, string $text): StoryMessageSequence
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

            if (!$executionResults->isEmpty()) {
                return $executionResults->merge(
                    $this->currentStatusMessages($tgUser)
                );
            }
        }

        // story switch command
        if (preg_match("#^/story(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = $matches[1];

            $story = $this->storyRepository->get($storyId);

            if ($story) {
                return $this->switchToStory($tgUser, $story);
            }

            return StoryMessageSequence::mash(
                new TextMessage('История с id = ' . $storyId . ' не найдена.'),
                $this->currentStatusMessages($tgUser)
            );
        }

        if ($text === Story::STORY_SELECTION_COMMAND || $text === '/story') {
            return $this->storySelection();
        }

        // default - next step
        return $this->nextStep($tgUser, $text);
    }

    private function startCommand(TelegramUser $tgUser): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);
        $isReader = $status !== null;

        $greeting = $isReader ? 'С возвращением' : 'Добро пожаловать';
        $greeting .= ', <b>' . $tgUser->privateName() . '</b>!';

        $sequence = StoryMessageSequence::make(
            new TextMessage($greeting)
        );

        if (!$tgUser->hasGender()) {
            return $sequence->add(
                $this->askGender()
            );
        }

        return $sequence->merge(
            $this->storySelection()
        );
    }

    private function readGender(TelegramUser $tgUser, string $text): StoryMessageSequence
    {
        /** @var integer|null */
        $gender = null;

        switch ($text) {
            case $this->masAction:
                $gender = Gender::MAS;
                break;

            case $this->femAction:
                $gender = Gender::FEM;
                break;
        }

        $genderIsOk = ($gender !== null);

        if (!$genderIsOk) {
            return new StoryMessageSequence(
                new TextMessage('Вы написали что-то не то. 🤔'),
                $this->askGender()
            );
        }

        $tgUser->genderId = $gender;

        $this->telegramUserRepository->save($tgUser);

        return StoryMessageSequence::mash(
            new TextMessage(
                'Спасибо, уважаем{ый 👦|ая 👧}, ' .
                'ваш пол сохранен и теперь будет учитываться. 👌'
            ),
            $this->storySelection()
        );
    }

    private function askGender(): MessageInterface
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

    private function executeStoryCommand(
        TelegramUser $tgUser,
        string $command
    ): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            return StoryMessageSequence::empty();
        }

        $story = $this->storyRepository->get($status->storyId);

        Assert::notNull($story);

        return $story->executeCommand($command);
    }

    /**
     * Starts the default story or continues the current one.
     */
    private function startOrContinueStory(TelegramUser $tgUser): StoryMessageSequence
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

    private function continueStory(StoryStatus $status): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage('Итак, продолжим...'),
            $this->statusToMessages($status)
        );
    }

    private function startStory(TelegramUser $tgUser, int $storyId): StoryMessageSequence
    {
        $story = $this->storyRepository->get($storyId);

        $sequence = StoryMessageSequence::mash(
            new TextMessage('Итак, начнем...'),
            $story->start($tgUser)
        );

        $this->storyStatusRepository->store([
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => $story->id(),
            'step_id' => $sequence->nodeId(),
            'json_data' => json_encode($sequence->data())
        ]);

        return $sequence;
    }

    private function nextStep(TelegramUser $tgUser, string $text): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        $cluelessMessage = new TextMessage('Что-что? Повторите-ка... 🧐');

        if (!$status) {
            return StoryMessageSequence::mash(
                $cluelessMessage,
                $this->storySelection()
            );
        }

        $story = $this->storyRepository->get($status->storyId);
        $node = $story->getNode($status->stepId);

        Assert::notNull($node);

        $data = $story->makeData($status->data());

        $sequence = $story->go($tgUser, $node, $data, $text);

        if ($sequence) {
            $status->stepId = $sequence->nodeId();
            $status->jsonData = json_encode($sequence->data());

            $this->storyStatusRepository->save($status);

            return $sequence;
        }

        return StoryMessageSequence::mash(
            $cluelessMessage,
            $this->currentStatusMessages($tgUser)
        );
    }

    private function storySelection(): StoryMessageSequence
    {
        $stories = $this->storyRepository->getAllPublished();

        $sequence = ($stories->isEmpty())
            ? StoryMessageSequence::make(
                new TextMessage('⛔ Историй нет.', 'Что-то явно пошло не так.')
            )
            : $stories->toInfo();

        return
            StoryMessageSequence::mash(
                new TextMessage('Выберите историю:'),
                $sequence
            )
            ->finalize();
    }

    private function switchToStory(TelegramUser $tgUser, Story $story): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            return $this->startStory($tgUser, $story->id());
        }

        $sequence = $story->start($tgUser);

        $status->storyId = $story->id();
        $status->stepId = $sequence->nodeId();
        $status->jsonData = json_encode($sequence->data());

        $this->storyStatusRepository->save($status);

        return $sequence;
    }

    private function currentStatusMessages(TelegramUser $tgUser): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        return $this->statusToMessages($status);
    }

    private function statusToMessages(StoryStatus $status): StoryMessageSequence
    {
        $story = $this->storyRepository->get($status->storyId);

        $node = $story->getNode($status->stepId);
        $data = $story->makeData($status->data());

        return $story->renderNode(
            $status->telegramUser(),
            $node,
            $data
        );
    }

    private function getStatus(TelegramUser $tgUser): ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($tgUser);
    }
}
