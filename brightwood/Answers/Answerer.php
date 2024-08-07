<?php

namespace Brightwood\Answers;

use App\Core\Interfaces\LinkerInterface;
use App\External\Interfaces\TelegramTransportInterface;
use App\External\TelegramTransport;
use App\Models\TelegramUser;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Models\BotCommand;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Exception;
use Plasticode\Semantics\Gender;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Strings;
use Plasticode\Util\Text;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * Returns story message sequence in answer to a text from a telegram user.
 *
 * Has some other side effects (and this is not good):
 *
 * - Can change telegram users (set gender), but doesn't save them.
 * - Can create and change story statuses.
 */
class Answerer
{
    use LoggerAwareTrait;

    const BRIGHTWOOD_STAGE = 'brightwood_stage';

    private const STAGE_UPLOAD = 'upload';
    private const MAX_JSON_SIZE = 1024 * 1024; // 1 Mb
    private const MAX_JSON_SIZE_NAME = '1 Мб';

    private string $masAction = '👦 Мальчик';
    private string $femAction = '👧 Девочка';

    private SettingsProviderInterface $settingsProvider;
    private LinkerInterface $linker;

    private StoryStatusRepositoryInterface $storyStatusRepository;
    private StoryService $storyService;
    private TelegramTransportInterface $telegram;

    public function __construct(
        LoggerInterface $logger,
        SettingsProviderInterface $settingsProvider,
        LinkerInterface $linker,
        StoryStatusRepositoryInterface $storyStatusRepository,
        StoryService $storyService,
        TelegramTransportFactory $telegramFactory
    )
    {
        $this->withLogger($logger);

        $this->settingsProvider = $settingsProvider;
        $this->linker = $linker;

        $this->storyStatusRepository = $storyStatusRepository;
        $this->storyService = $storyService;
        $this->telegram = ($telegramFactory)();
    }

    public function getAnswers(
        TelegramUser $tgUser,
        ?string $text = null,
        ?array $documentInfo = null
    ): StoryMessageSequence
    {
        // start command
        if (Strings::startsWith($text, BotCommand::CODE_START)) {
            return $this->startCommand($tgUser);
        }

        // check gender
        // todo: use stage for this? but must be mandatory
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
            return $this->storySelection($tgUser);
        }

        if ($text === BotCommand::CODE_EDIT) {
            return $this->storyEditing($tgUser);
        }

        if ($text === BotCommand::CODE_NEW) {
            return $this->storyCreation();
        }

        if ($text === BotCommand::CODE_UPLOAD) {
            return $this->storyUpload($tgUser);
        }

        $stage = $tgUser->getMetaValue(self::BRIGHTWOOD_STAGE);

        if ($stage === self::STAGE_UPLOAD) {
            if (empty($documentInfo)) {
                return
                    StoryMessageSequence::text(
                        '❌ Пожалуйста, загрузите документ.',
                        $this->uploadTips()
                    )
                    ->withStage(self::STAGE_UPLOAD) // we are still on this stage
                    ->finalize();
            }

            return $this->processDocument($tgUser, $documentInfo);
        }

        if (strlen($text) === 0) {
            return StoryMessageSequence::text('🧾 Я понимаю только сообщения с текстом.');
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

        $sequence = StoryMessageSequence::text($greeting);

        if (!$tgUser->hasGender()) {
            return $sequence->add(
                $this->askGender()
            );
        }

        return $sequence->merge(
            $this->storySelection($tgUser)
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

        $tgUser->withGenderId($gender);

        return StoryMessageSequence::mash(
            new TextMessage(
                'Спасибо, уважаем{ый 👦|ая 👧}, ' .
                'ваш пол сохранен и теперь будет учитываться. 👌'
            ),
            $this->storySelection($tgUser)
        );
    }

    private function askGender(): MessageInterface
    {
        return new Message(
            ['Для корректного текста историй, пожалуйста, укажите ваш <b>пол</b>:'],
            [$this->masAction, $this->femAction]
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
                $this->storySelection($tgUser)
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

    private function storySelection(TelegramUser $tgUser): StoryMessageSequence
    {
        $stories = $this->storyService->getStoriesPlayableBy($tgUser);

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
            new TextMessage('Создать новую историю: ' . BotCommand::CODE_NEW),
            new TextMessage('Загрузить новую или отредактированную историю: ' . BotCommand::CODE_UPLOAD)
        );

        return $sequence->finalize();
    }

    private function editStoryLink(Story $story): StoryMessageSequence
    {
        return StoryMessageSequence::makeFinalized(
            new TextMessage(
                "Для редактирования истории <b>{$story->title()}</b> перейдите по ссылке:",
                $this->buildStoryEditUrl($story),
                $this->editorTips()
            )
        );
    }

    private function storyCreation(): StoryMessageSequence
    {
        return StoryMessageSequence::makeFinalized(
            new TextMessage(
                'Для создания новой истории перейдите по ссылке:',
                $this->buildStoryCreationUrl(),
                $this->editorTips()
            )
        );
    }

    private function storyUpload(): StoryMessageSequence
    {
        return
            StoryMessageSequence::text(
                'Загрузите JSON-файл истории, экспортированный из редактора. 👇',
                $this->uploadTips()
            )
            ->withStage(self::STAGE_UPLOAD)
            ->finalize();
    }

    private function editorTips(): string
    {
        return Text::join([
            '🔹 Рекомендуем открывать редактор на компьютере или планшете.',
            '🔹 После завершения работы на историей экспортируйте ее в JSON-файл и загрузите его сюда, используя команду ' . BotCommand::CODE_UPLOAD
        ]);
    }

    private function uploadTips(): string
    {
        return 'Отмена загрузки: ' . BotCommand::CODE_CANCEL_UPLOAD;
    }

    private function processDocument(
        TelegramUser $tgUser,
        array $documentInfo
    ): StoryMessageSequence
    {
        try {
            $mimeType = $documentInfo['mime_type'];
            $fileId = $documentInfo['file_id'];
            $fileSize = $documentInfo['file_size'];

            // 1. check the mime_type
            if ($mimeType !== 'application/json') {
                throw new Exception('Неверный тип файла, загрузите JSON, полученный из редактора, пожалуйста.');
            }

            // 2. check the file size
            if ($fileSize > self::MAX_JSON_SIZE) {
                throw new Exception('Размер файла не может превышать ' . self::MAX_JSON_SIZE_NAME . '. Загрузите файл поменьше, пожалуйста.');
            }

            // 3. get a file link
            $response = $this->telegram->executeCommand(
                TelegramTransport::COMMAND_GET_FILE,
                ['file_id' => $fileId]
            );

            $responseObject = json_decode($response, true);

            if ($responseObject['ok'] !== true) {
                throw new Exception('Не удалось получить файл от Telegram, попробуйте еще раз.');
            }

            $filePath = $responseObject['result']['file_path'];

            // 4. download the file
            $fileUrl = $this->telegram->getFileUrl($filePath);
            $json = file_get_contents($fileUrl);

            // 5. validate JSON, check that it can be parsed
            $jsonArray = json_decode($json, true);

            if (empty($jsonArray)) {
                throw new Exception('Файл поврежден. Пожалуйста, загрузите валидный JSON-файл.');
            }

            // 6. get story id, check that it's a valid uuid (!!!)
            $storyUuid = $jsonArray['id'];

            if (!$this->isValidUuid($storyUuid)) {
                throw new Exception('Story id must be a valid uuid4.');
            }

            // 7. check that a story created from JSON passes validation
            $this->storyService->makeStoryFromJson($json);

            // 8. store the JSON to the user's story candidate record
            $storyCandidate = $this->storyService->saveStoryCandidate($tgUser, $json);

            // 9. get the story by id
            $story = $this->storyService->getStoryByUuid($storyUuid);

            // 10. if the story doesn't exist, create new story
            if (!$story) {
                $story = $this->storyService->createStoryFromCandidate(
                    $storyUuid,
                    $storyCandidate
                );

                return StoryMessageSequence::text(
                    "Новая история <b>{$story->title()}</b> успешно создана!",
                    'Играть: ' . $story->toCommand()->codeString()
                )->finalize();
            }

            // 11. if the story exists, check that the current user can update the story
            throw new Exception('Not implemented yet.');

            // 12. if they can't update it, tell them that access is denied, but they can save it as a new story (mark it as a fork?)

            // 13. if they can update it, ask them if they want to create a new version or create a new story (mark it as a fork again)
        } catch (Exception $ex) {
            return 
                StoryMessageSequence::text(
                    '❌ ' . $ex->getMessage(),
                    $this->uploadTips()
                )
                ->withStage(self::STAGE_UPLOAD) // yes, we are still on this stage
                ->finalize();
        }
    }

    private function isValidUuid(string $uuid): bool
    {
        return preg_match(
            "#^[a-f0-9]{32}$#i",
            str_replace('-', '', $uuid)
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
