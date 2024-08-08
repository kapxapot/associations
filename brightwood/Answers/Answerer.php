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
use Brightwood\Models\StoryCandidate;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Brightwood\Util\Uuid;
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
    private const STAGE_EXISTING_STORY = 'existing_story';
    private const STAGE_NOT_ALLOWED_STORY = 'not_allowed_story';

    private const MAX_JSON_SIZE = 1024 * 1024; // 1 Mb
    private const MAX_JSON_SIZE_NAME = '1 Мб';

    private const ACTION_MAS = '👦 Мальчик';
    private const ACTION_FEM = '👧 Девочка';

    private const ACTION_UPDATE_STORY = 'Обновить';
    private const ACTION_NEW_STORY = 'Создать новую';

    private const ACTION_CANCEL = 'Отмена';

    private const MESSAGE_CLUELESS = 'Что-что? Повторите-ка... 🧐';

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
                $playable = $this->storyService->isStoryPlayableBy($story, $tgUser);

                if ($playable) {
                    return $this->startStory($tgUser, $story);
                }
            }

            return $this->errorMessage(
                $tgUser,
                "История с id = {$storyId} не найдена."
            );
        }

        // story edit command
        if (preg_match("#^/edit(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($story) {
                $editable = $this->storyService->isStoryEditableBy($story, $tgUser);

                if ($editable) {
                    return $this->editStoryLink($story);
                }
            }

            return $this->errorMessage(
                $tgUser,
                "История с id = {$storyId} не найдена."
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

        if ($text === BotCommand::CODE_CANCEL_UPLOAD) {
            if (in_array($stage, $this->uploadStages())) {
                return $this->uploadCanceled();
            }
        }

        $documentUploaded = !empty($documentInfo);

        if ($stage === self::STAGE_UPLOAD) {
            if (!$documentUploaded) {
                return
                    StoryMessageSequence::text(
                        '❌ Пожалуйста, загрузите документ.',
                        $this->uploadTips()
                    )
                    ->withStage(self::STAGE_UPLOAD) // we are still on this stage
                    ->finalize();
            }

            return $this->processUpload($tgUser, $documentInfo);
        }

        if ($stage === self::STAGE_EXISTING_STORY || $stage === self::STAGE_NOT_ALLOWED_STORY) {
            return $this->processOverwrite($tgUser, $stage, $text);
        }

        if (strlen($text) === 0) {
            if ($documentUploaded) {
                return StoryMessageSequence::text(
                    '❌ Вы загрузили документ, но не там, где нужно.',
                    'Если вы хотите загрузить историю, используйте команду ' . BotCommand::CODE_UPLOAD
                )
                ->finalize();
            }

            return StoryMessageSequence::text('❌ Я понимаю только сообщения с текстом.')
                ->finalize();
        }

        // default - next step
        return $this->nextStep($tgUser, $text);
    }

    private function errorMessage(TelegramUser $tgUser, string $message): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage("❌ {$message}"),
            $this->currentStatusMessages($tgUser)
        );
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
            case self::ACTION_MAS:
                $gender = Gender::MAS;
                break;

            case self::ACTION_FEM:
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
            [self::ACTION_MAS, self::ACTION_FEM]
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

        return $this->getStatusStory($status)->executeCommand($command);
    }

    private function continueStory(StoryStatus $status): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage('Итак, продолжим...'),
            $this->statusToMessages($status)
        );
    }

    private function nextStep(TelegramUser $tgUser, string $text): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        $cluelessMessage = new TextMessage(self::MESSAGE_CLUELESS);

        if (!$status) {
            return StoryMessageSequence::mash(
                $cluelessMessage,
                $this->storySelection($tgUser)
            );
        }

        $story = $this->getStatusStory($status);
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

    private function startStory(TelegramUser $tgUser, Story $story): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        if (!$status) {
            $status = StoryStatus::create();
            $status->telegramUserId = $tgUser->getId();
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
        return 'Отменить загрузку: ' . BotCommand::CODE_CANCEL_UPLOAD;
    }

    private function processUpload(
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
            $jsonData = json_decode($json, true);

            if (empty($jsonData)) {
                throw new Exception('Файл поврежден. Пожалуйста, загрузите валидный JSON-файл.');
            }

            // 6. get story id, check that it's a valid uuid (!!!)
            $storyUuid = $jsonData['id'];

            if (!Uuid::isValid($storyUuid)) {
                throw new Exception('Story id must be a valid uuid4.');
            }

            // 7. check that a story created from JSON passes validation
            $this->storyService->makeStoryFromJson($json);

            // 8. store the JSON to the user's story candidate record
            $storyCandidate = $this->storyService->saveStoryCandidate($tgUser, $jsonData);

            // 9. get the story by id
            $story = $this->storyService->getStoryByUuid($storyUuid);

            // 10. if the story doesn't exist, create new story
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            // 11. if the story exists, check that the current user can update the story
            $canUpdate = $this->storyService->isStoryEditableBy($story, $tgUser);

            // 12. if they can update it, ask them if they want to create a new version or create a new story (mark the original story as a source story)
            if ($canUpdate) {
                $sequence = StoryMessageSequence::text(
                    "⚠ История <b>{$story->title()}</b> уже существует.",
                    'Вы хотите обновить ее или создать новую?',
                    $this->uploadTips()
                );

                return $this->stage($sequence, self::STAGE_EXISTING_STORY);
            }

            // 13. if they can't update it, tell them that access is denied, but they can save it as a new story (mark the original story as a source story)
            $sequence = StoryMessageSequence::text(
                '⛔ Вы пытаетесь загрузить историю, к которой у вас нет доступа.',
                'Строго говоря, вам не следует этого делать. 🤔',
                'Но раз мы уже здесь, то вы можете создать новую историю.',
                'Cоздать новую историю?',
                $this->uploadTips()
            );

            return $this->stage($sequence, self::STAGE_NOT_ALLOWED_STORY);
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

    private function processOverwrite(
        TelegramUser $tgUser,
        string $stage,
        string $text
    ): StoryMessageSequence
    {
        if ($text === self::ACTION_CANCEL) {
            $this->storyService->deleteStoryCandidateFor($tgUser);
            return $this->uploadCanceled();
        }

        $storyCandidate = $this->storyService->getStoryCandidateFor($tgUser);

        if (!$storyCandidate) {
            $this->logger->error("Story candidate for Telegram user [{$tgUser->getId()}] not found.");

            return StoryMessageSequence::text('❌ Ошибка загрузки. Попробуйте еще раз.')
                ->finalize();
        }

        if ($stage === self::STAGE_EXISTING_STORY && $text === self::ACTION_UPDATE_STORY) {
            $story = $this->storyService->getStoryByUuid($storyCandidate->uuid);

            // if the story was deleted for some reason...
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            $updatedStory = $this->storyService->updateStory($story, $storyCandidate);

            return StoryMessageSequence::text(
                "✅ История <b>{$updatedStory->title()}</b> успешно обновлена!",
                'Играть: ' . $updatedStory->toCommand()->codeString()
            )->finalize();
        }

        if ($text === self::ACTION_NEW_STORY) {
            return $this->newStory($storyCandidate, Uuid::new());
        }

        // we stay put
        return $this->stage(
            StoryMessageSequence::text(
                self::MESSAGE_CLUELESS,
                $this->uploadTips()
            ),
            $stage
        );
    }

    private function newStory(
        StoryCandidate $storyCandidate,
        ?string $uuid = null
    ): StoryMessageSequence
    {
        $newStory = $this->storyService->newStory($storyCandidate, $uuid);

        return StoryMessageSequence::text(
            "✅ Новая история <b>{$newStory->title()}</b> успешно создана!",
            'Играть: ' . $newStory->toCommand()->codeString()
        )->finalize();
    }

    /**
     * @throws Exception
     */
    private function stage(StoryMessageSequence $sequence, string $stage): StoryMessageSequence
    {
        if ($stage === self::STAGE_EXISTING_STORY) {
            return $sequence
                ->withStage($stage)
                ->withActions(
                    self::ACTION_UPDATE_STORY,
                    self::ACTION_NEW_STORY,
                    self::ACTION_CANCEL
                );
        }

        if ($stage === self::STAGE_NOT_ALLOWED_STORY) {
            return $sequence
                ->withStage($stage)
                ->withActions(
                    self::ACTION_NEW_STORY,
                    self::ACTION_CANCEL
                );
        }

        throw new Exception('Unknown stage: ' . $stage);
    }

    private function uploadCanceled(): StoryMessageSequence
    {
        return StoryMessageSequence::text('✅ Загрузка истории отменена.')
            ->finalize();;
    }

    private function currentStatusMessages(TelegramUser $tgUser): StoryMessageSequence
    {
        $status = $this->getStatus($tgUser);

        Assert::notNull($status);

        return $this->statusToMessages($status);
    }

    private function statusToMessages(StoryStatus $status): StoryMessageSequence
    {
        $story = $this->getStatusStory($status);
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

    private function getStatusStory(StoryStatus $status): Story
    {
        return $this->storyService->applyVersion(
            $status->story(),
            $status->storyVersion()
        );
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

    /**
     * @return string[]
     */
    private function uploadStages(): array
    {
        return [
            self::STAGE_UPLOAD,
            self::STAGE_EXISTING_STORY,
            self::STAGE_NOT_ALLOWED_STORY
        ];
    }
}
