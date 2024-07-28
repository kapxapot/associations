<?php

namespace Brightwood\Answers;

use App\Core\Interfaces\LinkerInterface;
use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\BotCommand;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Plasticode\Semantics\Gender;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
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

    private string $masAction = '👦 Мальчик';
    private string $femAction = '👧 Девочка';

    private SettingsProviderInterface $settingsProvider;
    private LinkerInterface $linker;

    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private StoryService $storyService;

    public function __construct(
        LoggerInterface $logger,
        SettingsProviderInterface $settingsProvider,
        LinkerInterface $linker,
        StoryStatusRepositoryInterface $storyStatusRepository,
        TelegramUserRepositoryInterface $telegramUserRepository,
        StoryService $storyService
    )
    {
        $this->withLogger($logger);

        $this->settingsProvider = $settingsProvider;
        $this->linker = $linker;

        $this->storyStatusRepository = $storyStatusRepository;
        $this->telegramUserRepository = $telegramUserRepository;

        $this->storyService = $storyService;
    }

    public function getAnswers(TelegramUser $tgUser, string $text): StoryMessageSequence
    {
        // start command
        if (Strings::startsWith($text, BotCommand::CODE_START)) {
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
            $storyId = (int)$matches[1];

            $story = $this->getStory($storyId);

            if ($story) {
                return $this->switchToStory($tgUser, $story);
            }

            return StoryMessageSequence::mash(
                new TextMessage("История с id = {$storyId} не найдена."),
                $this->currentStatusMessages($tgUser)
            );
        }

        // story edit command
        if (preg_match("#^/edit(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];

            $story = $this->getStory($storyId);

            if ($story) {
                return $this->editStoryLink($story);
            }

            return StoryMessageSequence::mash(
                new TextMessage("История с id = {$storyId} не найдена."),
                $this->currentStatusMessages($tgUser)
            );
        }

        if ($text === BotCommand::STORY_SELECTION || $text === BotCommand::CODE_STORY) {
            return $this->storySelection();
        }

        if ($text === BotCommand::CODE_EDIT) {
            return $this->storyEditing($tgUser);
        }

        if ($text === BotCommand::CODE_NEW) {
            return $this->storyCreation();
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

        return $status->story()->executeCommand($command);
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
            $this->storyService->getDefaultStoryId()
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
        $story = $this->getStory($storyId);

        $sequence = StoryMessageSequence::mash(
            new TextMessage('Итак, начнем...'),
            $story->start($tgUser)
        );

        $storyVersion = $story->currentVersion();

        $this->storyStatusRepository->store([
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => $story->getId(),
            'story_version_id' => $storyVersion ? $storyVersion->getId() : null,
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

        $story = $status->story();
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
        $stories = $this->storyService->getStories();

        if ($stories->isEmpty()) {
            return StoryMessageSequence::makeFinalized(
                new TextMessage('⛔ Историй нет.')
            );
        }

        return
            StoryMessageSequence::mash(
                new TextMessage('Выберите историю:'),
                $stories->toInfo()
            )
            ->finalize();
    }

    private function switchToStory(TelegramUser $tgUser, Story $story): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            return $this->startStory($tgUser, $story->getId());
        }

        $sequence = $story->start($tgUser);

        $storyVersion = $story->currentVersion();

        $status->storyId = $story->getId();
        $status->storyVersionId = $storyVersion ? $storyVersion->getId() : null;
        $status->stepId = $sequence->nodeId();
        $status->jsonData = json_encode($sequence->data());

        $this->storyStatusRepository->save($status);

        return $sequence;
    }

    private function storyEditing(TelegramUser $tgUser): StoryMessageSequence
    {
        $sequence = StoryMessageSequence::empty();
        $stories = $this->storyService->getStoriesEditableBy($tgUser);

        if ($stories->isEmpty()) {
            $sequence->add(
                new TextMessage('⛔ У вас нет доступных историй для редактирования.')
            );
        } else {
            $sequence->add(
                new TextMessage('Вы можете редактировать следующие истории:'),
                ...$stories->map(
                    fn (Story $s) => new TextMessage(
                        sprintf(
                            '%s %s_%s',
                            $s->title(),
                            BotCommand::CODE_EDIT,
                            $s->getId()
                        )
                    )
                )
            );
        }

        $sequence->add(
            new TextMessage('Создать новую историю: ' . BotCommand::CODE_NEW)
        );

        return $sequence->finalize();
    }

    private function editStoryLink(Story $story): StoryMessageSequence
    {
        return StoryMessageSequence::makeFinalized(
            new TextMessage(
                "Для редактирования истории <b>{$story->title()}</b> перейдите по ссылке:",
                $this->buildStoryEditUrl($story),
                '(Рекомендуем открывать редактор на компьютере или планшете.)'
            )
        );
    }

    private function storyCreation(): StoryMessageSequence
    {
        return StoryMessageSequence::makeFinalized(
            new TextMessage(
                'Для создания новой истории перейдите по ссылке:',
                $this->buildStoryCreationUrl(),
                '(Рекомендуем открывать редактор на компьютере или планшете.)'
            )
        );
    }

    private function currentStatusMessages(TelegramUser $tgUser): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        return $this->statusToMessages($status);
    }

    private function statusToMessages(StoryStatus $status): StoryMessageSequence
    {
        $story = $status->story();
        $node = $story->getNode($status->stepId);
        $data = $story->makeData($status->data());

        return $story->renderNode(
            $status->telegramUser(),
            $node,
            $data
        );
    }

    private function getStory(int $storyId): ?Story
    {
        return $this->storyService->getStory($storyId);
    }

    private function getStatus(TelegramUser $tgUser): ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($tgUser);
    }

    private function buildStoryEditUrl(Story $story): string
    {
        return sprintf(
            '%s?edit=%s',
            $this->getBuilderUrl(),
            $this->linker->abs(
                $this->linker->story($story)
            )
        );
    }

    private function buildStoryCreationUrl(): string
    {
        return sprintf(
            '%s?new',
            $this->getBuilderUrl()
        );
    }

    private function getBuilderUrl(): string
    {
        return $this->settingsProvider->get(
            'brightwood.builder_url',
            'https://brightwood-builder.onrender.com'
        );
    }
}
