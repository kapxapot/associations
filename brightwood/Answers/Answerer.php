<?php

namespace Brightwood\Answers;

use App\Core\Interfaces\LinkerInterface;
use App\External\Interfaces\TelegramTransportInterface;
use App\External\TelegramTransport;
use App\Models\TelegramUser;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Models\BotCommand;
use Brightwood\Models\Language;
use Brightwood\Models\Messages\Message;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Brightwood\Models\MetaKey;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryCandidate;
use Brightwood\Models\StoryStatus;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Brightwood\Services\TelegramUserService;
use Brightwood\Util\Format;
use Brightwood\Util\Join;
use Brightwood\Util\Uuid;
use Exception;
use InvalidArgumentException;
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Semantics\Gender;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Arrays;
use Plasticode\Util\Sort;
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

    const DEFAULT_LANGUAGE = Language::EN;

    private const STAGE_GENDER = 'gender';
    private const STAGE_LANGUAGE = 'language';
    private const STAGE_STORY = 'story';
    private const STAGE_UPLOAD = 'upload';
    private const STAGE_EXISTING_STORY = 'existing_story';
    private const STAGE_NOT_ALLOWED_STORY = 'not_allowed_story';

    private const MB = 1024 * 1024; // 1 Mb
    private const MAX_JSON_SIZE = 1; // Mb

    private const ACTION_EN = '🇬🇧 English';
    private const ACTION_RU = '🇷🇺 Русский';

    private const ACTION_MAS = '👦 [[Boy]]';
    private const ACTION_FEM = '👧 [[Girl]]';

    private const ACTION_UPDATE_STORY = '♻ [[Update]]';
    private const ACTION_NEW_STORY = '🌱 [[Create new]]';

    private const ACTION_CANCEL = '❌ [[Cancel]]';

    private const MESSAGE_CLUELESS = '[[Huh? I didn\'t get it...]] 🧐';
    private const MESSAGE_STORY_NOT_FOUND = '[[Story with id = {story_id} not found.]]';

    private SettingsProviderInterface $settingsProvider;
    private LinkerInterface $linker;

    private StoryStatusRepositoryInterface $storyStatusRepository;
    private StoryService $storyService;
    private StoryParser $parser;
    private TelegramUserService $telegramUserService;
    private TelegramTransportInterface $telegram;

    private TelegramUser $tgUser;
    private string $tgLangCode;

    public function __construct(
        LoggerInterface $logger,
        SettingsProviderInterface $settingsProvider,
        LinkerInterface $linker,
        StoryStatusRepositoryInterface $storyStatusRepository,
        StoryService $storyService,
        StoryParser $parser,
        TelegramUserService $telegramUserService,
        TelegramTransportFactory $telegramFactory,
        TelegramUser $tgUser,
        string $tgLangCode
    )
    {
        $this->withLogger($logger);

        $this->settingsProvider = $settingsProvider;
        $this->linker = $linker;

        $this->storyStatusRepository = $storyStatusRepository;
        $this->storyService = $storyService;
        $this->parser = $parser;
        $this->telegram = ($telegramFactory)();
        $this->telegramUserService = $telegramUserService;

        $this->tgUser = $tgUser;
        $this->tgLangCode = $tgLangCode;
    }

    public function getAnswers(
        ?string $text = null,
        ?array $documentInfo = null
    ): StoryMessageSequence
    {
        $stage = $this->getMetaValue(MetaKey::STAGE);

        // check language
        if ($stage === self::STAGE_LANGUAGE) {
            return $this->readLanguage($text);
        }

        // check gender
        if ($stage === self::STAGE_GENDER) {
            return $this->readGender($text);
        }

        // start command
        if ($text === BotCommand::CODE_START) {
            return $this->startCommand();
        }

        // check required stages
        $requiredStageMessages = $this->getRequiredStageMessages();

        if (!$requiredStageMessages->isEmpty()) {
            return $requiredStageMessages;
        }

        // try executing story-specific commands
        // probably, this can be moved to the bottom when we check the current story
        // but this way the story's commands override all the global ones
        if (Strings::startsWith($text, '/')) {
            $executionResults = $this->executeStoryCommand($text);

            if (!$executionResults->isEmpty()) {
                return $executionResults->merge(
                    $this->whereAreWe()
                );
            }
        }

        // story switch command
        if (preg_match("#^/story(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($this->isPlayable($story)) {
                return $this->showStory($story);
            }

            return $this->errorMessage(
                self::MESSAGE_STORY_NOT_FOUND,
                ['story_id' => $storyId]
            );
        }

        // start story from its card
        if (
            $stage === self::STAGE_STORY
            && (
                $text === $this->parse(BotCommand::START_STORY)
                || $text === BotCommand::CODE_START_STORY
            )
        ) {
            $storyId = (int)$this->getMetaValue(MetaKey::STORY_ID);

            if ($storyId) {
                $story = $this->getStory($storyId);

                if ($story) {
                    return $this->tryStartStory($story);
                }
            }
        }

        // story edit command
        if (preg_match("#^/edit(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($this->isEditable($story)) {
                return $this->editStoryLink($story);
            }

            return $this->errorMessage(
                self::MESSAGE_STORY_NOT_FOUND,
                ['story_id' => $storyId]
            );
        }

        // story language command
        if (preg_match("#^/story_lang(?:\s+|_)(\w+)$#i", $text, $matches)) {
            $langCode = $matches[1];
            return $this->storySelection($langCode);
        }

        // translate the action label here
        if (
            $text === $this->parse(BotCommand::STORY_SELECTION)
            || $text === BotCommand::CODE_STORY
        ) {
            return $this->storySelection();
        }

        if ($text === BotCommand::CODE_EDIT) {
            return $this->storyEditing();
        }

        if ($text === BotCommand::CODE_NEW) {
            return $this->storyCreation();
        }

        if ($text === BotCommand::CODE_UPLOAD) {
            return $this->storyUpload();
        }

        if ($text === BotCommand::CODE_CANCEL_UPLOAD) {
            if (in_array($stage, $this->uploadStages())) {
                return $this->uploadCanceled();
            }
        }

        if ($text === BotCommand::CODE_LANGUAGE) {
            return $this->askLanguage();
        }

        if ($text === BotCommand::CODE_GENDER) {
            return $this->askGender();
        }

        $documentUploaded = !empty($documentInfo);

        if ($stage === self::STAGE_UPLOAD) {
            if (!$documentUploaded) {
                return
                    StoryMessageSequence::textFinalized(
                        '❌ [[Please, upload a document.]]',
                        $this->uploadTips()
                    )
                    ->withStage(self::STAGE_UPLOAD); // we are still on this stage
            }

            return $this->processUpload($documentInfo);
        }

        if ($stage === self::STAGE_EXISTING_STORY || $stage === self::STAGE_NOT_ALLOWED_STORY) {
            return $this->processOverwrite($stage, $text);
        }

        if (strlen($text) === 0) {
            if ($documentUploaded) {
                return
                    StoryMessageSequence::textFinalized(
                        '❌ [[You\'ve uploaded a document, but in a wrong place.]]',
                        '[[If you want to upload a story, use the {upload_command} command.]]'
                    )
                    ->withVar('upload_command', BotCommand::CODE_UPLOAD);
            }

            return StoryMessageSequence::textFinalized(
                '❌ [[I understand only messages with text.]]'
            );
        }

        // we checked everything unrelated to the current story
        // let's check if there IS a current story
        $status = $this->getStatus();

        if (!$status) {
            return $this->cluelessMessage();
        }

        // there is a current story + status
        $story = $this->getStatusStory($status);

        if ($text === $this->parse(BotCommand::RESTART)) {
            return $this->tryStartStory($story);
        }

        $nextStepSequence = $this->nextStep($story, $status, $text);

        return $nextStepSequence->or(
            $this->cluelessMessage()
        );
    }

    private function cluelessMessage(): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage(self::MESSAGE_CLUELESS),
            $this->whereAreWe()
        );
    }

    private function whereAreWe(): StoryMessageSequence
    {
        // check for required stages
        $requiredMessages = $this->getRequiredStageMessages();

        if (!$requiredMessages->isEmpty()) {
            return $requiredMessages;
        }

        $status = $this->getStatus();

        // new player
        if (!$status) {
            return $this->storySelection();
        }

        return $this->statusToMessages($status, true);
    }

    private function errorMessage(string $message, ?array $vars = null): StoryMessageSequence
    {
        return
            StoryMessageSequence::mash(
                new TextMessage("❌ {$message}"),
                $this->whereAreWe()
            )
            ->withVars($vars);
    }

    private function startCommand(): StoryMessageSequence
    {
        $greeting = $this->isNewPlayer()
            ? '[[Welcome, <b>{user_name}</b>!]]'
            : '[[Welcome back, <b>{user_name}</b>!]]';

        return
            StoryMessageSequence::mash(
                new TextMessage($greeting),
                $this->whereAreWe()
            )
            ->withVar('user_name', $this->tgUser->privateName());
    }

    /**
     * Checks if the user has to go through a required stage such as
     * gender or language selection.
     */
    private function getRequiredStageMessages(): StoryMessageSequence
    {
        $stages = [
            self::STAGE_LANGUAGE => [
                'check' => fn () => $this->tgUser->hasLanguageCode(),
                'messages' => fn () => $this->askLanguage(),
            ],
            self::STAGE_GENDER => [
                'check' => fn () => $this->tgUser->hasGender(),
                'messages' => fn () => $this->askGender(),
            ],
        ];

        foreach ($stages as $stage) {
            if (!($stage['check'])()) {
                return ($stage['messages'])();
            }
        }

        return StoryMessageSequence::empty();
    }

    private function readLanguage(string $text): StoryMessageSequence
    {
        /** @var string|null */
        $langCode = null;

        switch ($text) {
            case self::ACTION_EN:
                $langCode = Language::EN;
                break;

            case self::ACTION_RU:
                $langCode = Language::RU;
                break;
        }

        if (!$langCode) {
            return StoryMessageSequence::mash(
                new TextMessage('[[You\'ve written something wrong.]] 🤔'),
                $this->askLanguage()
            );
        }

        $this->tgUser->withLangCode($langCode);

        return
            StoryMessageSequence::mash(
                new TextMessage(
                    '[[Thank you! Your language preference has been saved and will now be taken into account.]] 👌',
                    '[[You can change your language at any time using the {language_command} command.]]'
                ),
                $this->whereAreWe()
            )
            ->withVar('language_command', BotCommand::CODE_LANGUAGE);
    }

    private function readGender(string $text): StoryMessageSequence
    {
        /** @var integer|null */
        $gender = null;

        // actions must be translated to be checked correctly
        switch ($text) {
            case $this->parse(self::ACTION_MAS):
                $gender = Gender::MAS;
                break;

            case $this->parse(self::ACTION_FEM):
                $gender = Gender::FEM;
                break;
        }

        if (!$gender) {
            return StoryMessageSequence::mash(
                new TextMessage('[[You\'ve written something wrong.]] 🤔'),
                $this->askGender()
            );
        }

        $this->tgUser->withGenderId($gender);

        return
            StoryMessageSequence::mash(
                new TextMessage(
                    '[[Thank you, dear {👦|👧}, your gender has been saved and will now be taken into account.]] 👌',
                    '[[You can change your gender at any time using the {gender_command} command.]]'
                ),
                $this->whereAreWe()
            )
            ->withVar('gender_command', BotCommand::CODE_GENDER);
    }

    private function askLanguage(): StoryMessageSequence
    {
        return
            StoryMessageSequence::make(
                new Message(
                    ['[[Please, select your preferred <b>language</b>]]:'],
                    [self::ACTION_EN, self::ACTION_RU]
                )
            )
            ->withStage(self::STAGE_LANGUAGE);
    }

    private function askGender(): StoryMessageSequence
    {
        return
            StoryMessageSequence::make(
                new Message(
                    ['[[For better story texts, please provide your <b>gender</b>]]:'],
                    [self::ACTION_MAS, self::ACTION_FEM]
                )
            )
            ->withStage(self::STAGE_GENDER);
    }

    private function executeStoryCommand(string $command): StoryMessageSequence
    {
        $status = $this->getStatus();

        if (!$status) {
            return StoryMessageSequence::empty();
        }

        return $this->getStatusStory($status)->executeCommand($command);
    }

    private function continueStory(StoryStatus $status): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage('[[Let\'s continue...]]'),
            $this->statusToMessages($status)
        );
    }

    private function nextStep(
        Story $story,
        StoryStatus $status,
        string $text
    ): StoryMessageSequence
    {
        $node = $story->getNode($status->stepId);

        Assert::notNull($node);

        try {
            $data = $story->loadData($status->data());
        } catch (InvalidArgumentException $ex) {
            return $this->failedToLoadStory();
        }

        $sequence = $story->go($this->tgUser, $node, $data, $text);

        if (!$sequence) {
            return StoryMessageSequence::empty();
        }

        $this->updateStatus($status, $sequence);

        return $sequence;
    }

    private function updateStatus(StoryStatus $status, StoryMessageSequence $sequence): void
    {
        $status->stepId = $sequence->nodeId();
        $status->jsonData = json_encode($sequence->data());

        $this->storyStatusRepository->save($status);
    }

    private function storySelection(?string $langCode = null): StoryMessageSequence
    {
        $stories = $this->storyService->getStoriesPlayableBy($this->tgUser);

        $groups = $stories->group(
            fn (Story $story) => Language::purifyCode($story->langCode)
        );

        $curLangCode = $langCode ?? $this->tgUser->languageCode();
        $curLangStories = $groups[$curLangCode] ?? null;
        $curLang = Language::fromCode($curLangCode);

        if (!$curLangStories || $curLangStories->isEmpty()) {
            return StoryMessageSequence::textFinalized("⛔ [[No stories in {language}.]]")
                ->withVar('language', $curLang);
        }

        $lines = $curLangStories->stringize(
            fn (Story $story) => sprintf(
                '%s %s',
                $story->title(),
                BotCommand::story($story)
            )
        );

        $sequence =
            StoryMessageSequence::text(
                "[[Select a story in {$curLang}]]:",
                ...$this->indexLines($lines)
            )
            ->withVar('language', $curLang);

        $groupInfos = ArrayCollection::empty();

        foreach ($groups as $langCode => $group) {
            if ($langCode === $curLangCode) {
                continue;
            }

            $groupInfos = $groupInfos->add([
                'language' => Language::fromCode($langCode),
                'stories' => $group,
                'count' => count($group),
            ]);
        }

        if (!empty($groupInfos)) {
            $sequence->addText(
                '[[There are also stories in other languages]]:',
                ...$groupInfos
                    ->orderBy('count', Sort::DESC)
                    ->map(
                        function (array $info) {
                            $language = $info['language'];

                            return sprintf(
                                '[[%s]] (%s) %s',
                                $language,
                                $info['count'],
                                $language->toCommand()->codeString()
                            );
                        }
                    )
            );
        }

        return $sequence->finalize();
    }

    private function showStory(Story $story): StoryMessageSequence
    {
        $text = [
            "<b>{$story->title()}</b>",
            $story->description(),
        ];

        $creator = $story->creator();

        if ($creator) {
            $you = $this->tgUser->user()->equals($creator);

            $text[] = Join::space(
                '[[Author]]:',
                '<b>' . $creator->displayName() . '</b>',
                $you ? '([[That\'s you!]])' : ''
            );
        }

        if ($this->isAdmin()) {
            $createdAt = $story->createdAt;
            $dates = ['[[Created at]]: ' . Format::utc($createdAt)];

            if ($story->currentVersion()) {
                $updatedAt = $story->currentVersion()->createdAt;
                $dates[] = '[[Updated at]]: ' . Format::utc($updatedAt);
            }

            $text[] = Text::join($dates);
        }

        $text[] = BotCommand::START_STORY . ': ' . BotCommand::CODE_START_STORY;

        $sequence = StoryMessageSequence::text(...$text);

        if ($this->isEditable($story)) {
            $sequence->addText('✒ [[Edit]]: ' . BotCommand::edit($story));
        }

        return $sequence
            ->withActions(BotCommand::START_STORY, BotCommand::STORY_SELECTION)
            ->withStage(self::STAGE_STORY)
            ->withMetaValue(MetaKey::STORY_ID, $story->getId());
    }

    private function tryStartStory(Story $story): StoryMessageSequence
    {
        if ($this->isPlayable($story)) {
            return $this->startStory($story);
        }

        return StoryMessageSequence::textFinalized(
            '[[You cannot access this story anymore, sorry.]]',
            '[[Please, select another story.]]'
        );
    }

    private function startStory(Story $story): StoryMessageSequence
    {
        $status = $this->getStatus();

        if (!$status) {
            $status = StoryStatus::create();
            $status->telegramUserId = $this->tgUser->getId();
        }

        $sequence = $story->start($this->tgUser);

        $storyVersion = $story->currentVersion();

        $status->storyId = $story->getId();
        $status->storyVersionId = $storyVersion ? $storyVersion->getId() : null;
        $status->stepId = $sequence->nodeId();
        $status->jsonData = json_encode($sequence->data());

        $this->storyStatusRepository->save($status);

        return $sequence;
    }

    private function storyEditing(): StoryMessageSequence
    {
        $stories = $this->storyService->getStoriesEditableBy($this->tgUser);

        $sequence = StoryMessageSequence::makeFinalized();

        if ($stories->isEmpty()) {
            $sequence->addText('⛔ [[You have no stories available for edit.]]');
        } else {
            $lines = $stories->stringize(
                fn (Story $story) => sprintf(
                    '%s %s',
                    $story->title(),
                    BotCommand::edit($story)
                )
            );

            $sequence->addText(
                '[[You can edit the following stories]]:',
                ...$this->indexLines($lines)
            );
        }

        return $sequence->addText(
            '[[Create a new story]]: ' . BotCommand::CODE_NEW,
            '[[Upload a new or an edited story]]: ' . BotCommand::CODE_UPLOAD
        );
    }

    private function editStoryLink(Story $story): StoryMessageSequence
    {
        return
            StoryMessageSequence::textFinalized(
                '[[Follow the link to edit the story <b>{story_title}</b>]]:',
                $this->buildStoryEditUrl($story),
                $this->editorTips()
            )
            ->withVars([
                'story_title' => $story->title(),
                'upload_command' => BotCommand::CODE_UPLOAD,
            ]);
    }

    private function storyCreation(): StoryMessageSequence
    {
        return
            StoryMessageSequence::textFinalized(
                '[[Follow the link to create a new story]]:',
                $this->buildStoryCreationUrl(),
                $this->editorTips()
            )
            ->withVar('upload_command', BotCommand::CODE_UPLOAD);
    }

    private function storyUpload(): StoryMessageSequence
    {
        return
            StoryMessageSequence::textFinalized(
                '[[Upload the story JSON file exported from the editor.]] 👇',
                $this->uploadTips()
            )
            ->withStage(self::STAGE_UPLOAD);
    }

    /**
     * Add {upload_command} var.
     */
    private function editorTips(): string
    {
        return Text::join([
            '🔹 ⚠ [[At the moment, the editor works correctly only on a <b>desktop</b>!]]',
            '🔹 [[After editing the story export it into a JSON file and upload it here, using the {upload_command} command.]]'
        ]);
    }

    private function uploadTips(): string
    {
        return '[[Cancel the upload]]: ' . BotCommand::CODE_CANCEL_UPLOAD;
    }

    private function processUpload(array $documentInfo): StoryMessageSequence
    {
        try {
            $mimeType = $documentInfo['mime_type'];
            $fileId = $documentInfo['file_id'];
            $fileSize = $documentInfo['file_size'];

            // 1. check the mime_type
            if ($mimeType !== 'application/json') {
                throw new Exception('[[Incorrect file type. Upload a JSON exported from the editor, please.]]');
            }

            // 2. check the file size
            if ($fileSize > self::MAX_JSON_SIZE * self::MB) {
                // translate the message right away because we need to set the var
                throw new Exception(
                    $this->parse(
                        '[[The file size cannot exceed {max_json_size} MB. Upload a smaller file, please.]]',
                        ['max_json_size' => self::MAX_JSON_SIZE]
                    )
                );
            }

            // 3. get a file link
            $response = $this->telegram->executeCommand(
                TelegramTransport::COMMAND_GET_FILE,
                ['file_id' => $fileId]
            );

            $responseObject = json_decode($response, true);

            if ($responseObject['ok'] !== true) {
                throw new Exception('[[Failed to get the file from Telegram, try again.]]');
            }

            $filePath = $responseObject['result']['file_path'];

            // 4. download the file
            $fileUrl = $this->telegram->getFileUrl($filePath);
            $json = file_get_contents($fileUrl);

            // 5. validate JSON, check that it can be parsed
            $jsonData = json_decode($json, true);

            if (empty($jsonData)) {
                throw new Exception('[[Invalid file. Upload a valid JSON file, please.]]');
            }

            // 6. get story id, check that it's a valid uuid (!!!)
            $storyUuid = $jsonData['id'];

            if (!Uuid::isValid($storyUuid)) {
                throw new Exception('[[Story id must be a valid uuid4.]]');
            }

            // 7. check that a story created from JSON passes validation
            $this->storyService->makeStoryFromJson($json);

            // 8. store the JSON to the user's story candidate record
            $storyCandidate = $this->storyService->saveStoryCandidate($this->tgUser, $jsonData);

            // 9. get the story by id
            $story = $this->storyService->getStoryByUuid($storyUuid);

            // 10. if the story doesn't exist, create new story
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            // 11. if the story exists, check that the current user can update the story
            $canUpdate = $this->isEditable($story);

            // 12. if they can update it, ask them if they want to create a new version or create a new story (mark the original story as a source story)
            if ($canUpdate) {
                $sequence =
                    StoryMessageSequence::text(
                        '⚠ [[The story <b>{story_title}</b> already exists.]]',
                        '[[Would you like to update it or to create a new one?]]',
                        $this->uploadTips()
                    )
                    ->withVar('story_title', $story->title());

                return $this->setStage($sequence, self::STAGE_EXISTING_STORY);
            }

            // 13. if they can't update it, tell them that access is denied, but they can save it as a new story (mark the original story as a source story)
            $sequence = StoryMessageSequence::text(
                '⛔ [[You are trying to upload a story that you don\'t have access to.]]',
                '[[Strictly speaking, you shouldn\'t do this.]] 🤔',
                '[[But since we are already here, you can create a new story.]]',
                '[[Create a new story?]]',
                $this->uploadTips()
            );

            return $this->setStage($sequence, self::STAGE_NOT_ALLOWED_STORY);
        } catch (Exception $ex) {
            return 
                StoryMessageSequence::textFinalized(
                    '❌ ' . $ex->getMessage(),
                    $this->uploadTips()
                )
                ->withStage(self::STAGE_UPLOAD); // yes, we are still on this stage
        }
    }

    private function processOverwrite(string $stage, string $text): StoryMessageSequence
    {
        // action label must be translated here
        if ($text === $this->parse(self::ACTION_CANCEL)) {
            $this->storyService->deleteStoryCandidateFor($this->tgUser);
            return $this->uploadCanceled();
        }

        $storyCandidate = $this->storyService->getStoryCandidateFor($this->tgUser);

        if (!$storyCandidate) {
            $this->logger->error("Story candidate for Telegram user [{$this->tgUser->getId()}] not found.");

            return StoryMessageSequence::textFinalized(
                '❌ [[Upload error. Try again.]]'
            );
        }

        // action label must be translated here
        if (
            $stage === self::STAGE_EXISTING_STORY
            && $text === $this->parse(self::ACTION_UPDATE_STORY)
        ) {
            $story = $this->storyService->getStoryByUuid($storyCandidate->uuid);

            // if the story was deleted for some reason...
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            $updatedStory = $this->storyService->updateStory($story, $storyCandidate);

            return
                StoryMessageSequence::textFinalized(
                    "✅ [[The story <b>{story_title}</b> was successfully updated!]]",
                    '[[Play]]: ' . BotCommand::story($updatedStory)
                )
                ->withVar('story_title', $updatedStory->title());
        }

        // action label must be translated here
        if ($text === $this->parse(self::ACTION_NEW_STORY)) {
            return $this->newStory($storyCandidate, Uuid::new());
        }

        // we stay put
        return $this->setStage(
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

        return
            StoryMessageSequence::textFinalized(
                "✅ [[A new story <b>{story_title}</b> has been successfully created!]]",
                '[[Play]]: ' . BotCommand::story($newStory)
            )
            ->withVar('story_title', $newStory->title());
    }

    /**
     * @throws Exception
     */
    private function setStage(StoryMessageSequence $sequence, string $stage): StoryMessageSequence
    {
        $sequence->withStage($stage);

        if ($stage === self::STAGE_EXISTING_STORY) {
            return $sequence->withActions(
                self::ACTION_UPDATE_STORY,
                self::ACTION_NEW_STORY,
                self::ACTION_CANCEL
            );
        }

        if ($stage === self::STAGE_NOT_ALLOWED_STORY) {
            return $sequence->withActions(
                self::ACTION_NEW_STORY,
                self::ACTION_CANCEL
            );
        }

        return $sequence;
    }

    private function uploadCanceled(): StoryMessageSequence
    {
        return StoryMessageSequence::textFinalized('✅ [[Story upload canceled.]]');
    }

    private function statusToMessages(
        StoryStatus $status,
        bool $omitFinish = false
    ): StoryMessageSequence
    {
        $story = $this->getStatusStory($status);

        if ($omitFinish && $story->isFinished($status)) {
            return StoryMessageSequence::makeFinalized();
        }

        $node = $story->getNode($status->stepId);

        try {
            $data = $story->loadData($status->data());
        } catch (InvalidArgumentException $ex) {
            return $this->failedToLoadStory();
        }

        return $story->renderNode(
            $status->telegramUser(),
            $node,
            $data
        );
    }

    private function failedToLoadStory(): StoryMessageSequence
    {
        return StoryMessageSequence::textFinalized(
            '⛔ [[Failed to load the story. Please, start again or select another story.]]'
        );
    }

    private function getStory(int $storyId): ?Story
    {
        return $this->storyService->getStory($storyId);
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
            '%s?edit=%s&lng=%s',
            $this->getBuilderUrl(),
            $this->linker->abs(
                $this->linker->story($story)
            ),
            $this->getLanguageCode()
        );
    }

    private function buildStoryCreationUrl(): string
    {
        return sprintf(
            '%s?new&lng=%s',
            $this->getBuilderUrl(),
            $this->getLanguageCode()
        );
    }

    private function getBuilderUrl(): string
    {
        return $this->settingsProvider->get(
            'brightwood.builder_url',
            'https://brightwood-builder.onrender.com'
        );
    }

    private function getLanguageCode(): string
    {
        return $this->tgUser->languageCode() ?? self::DEFAULT_LANGUAGE;
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

    private function parse(string $text, ?array $vars = null): string
    {
        return $this->parser->parse($this->tgUser, $text, $vars, $this->tgLangCode);
    }

    private function isNewPlayer(): bool
    {
        return !$this->getStatus();
    }

    private function getStatus(): ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($this->tgUser);
    }

    /**
     * @param string[]|ArrayableInterface $lines
     * @return string[]
     */
    private function indexLines($lines, ?int $start = null): array
    {
        $lines = array_values(Arrays::adopt($lines));
        $start = $start ?? 0;
        $result = [];

        foreach ($lines as $index => $line) {
            $result[] = sprintf('%s. %s', $start + $index + 1, $line);
        }

        return $result;
    }

    private function getMetaValue(string $key)
    {
        return $this->tgUser->getMetaValue($key);
    }

    private function isAdmin(): bool
    {
        return $this->telegramUserService->isAdmin($this->tgUser);
    }

    private function isPlayable(?Story $story): bool
    {
        return $story
            ? $this->storyService->isStoryPlayableBy($story, $this->tgUser)
            : false;
    }

    private function isEditable(?Story $story): bool
    {
        return $story
            ? $this->storyService->isStoryEditableBy($story, $this->tgUser)
            : false;
    }
}
