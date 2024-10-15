<?php

namespace Brightwood\Answers;

use App\External\Interfaces\TelegramTransportInterface;
use App\External\TelegramTransport;
use App\Models\TelegramUser;
use Brightwood\Answers\Stages\Core\StageContext;
use Brightwood\Answers\Stages\GenderStage;
use Brightwood\Answers\Stages\LanguageStage;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Models\Language;
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
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Plasticode\Util\Text;
use Psr\Log\LoggerInterface;

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

    private const MB = 1024 * 1024; // 1 MB
    private const MAX_JSON_SIZE = 1; // MB

    private StoryStatusRepositoryInterface $storyStatusRepository;
    private StoryService $storyService;
    private StoryParser $parser;
    private TelegramUserService $telegramUserService;
    private TelegramTransportInterface $telegram;
    private UrlBuilder $urlBuilder;

    private TelegramUser $tgUser;
    private string $tgLangCode;

    private LanguageStage $languageStage;
    private GenderStage $genderStage;

    public function __construct(
        LoggerInterface $logger,
        StoryStatusRepositoryInterface $storyStatusRepository,
        StoryService $storyService,
        StoryParser $parser,
        TelegramUserService $telegramUserService,
        TelegramTransportFactory $telegramFactory,
        UrlBuilder $urlBuilder,
        TelegramUser $tgUser,
        string $tgLangCode
    )
    {
        $this->withLogger($logger);

        $this->storyStatusRepository = $storyStatusRepository;
        $this->storyService = $storyService;
        $this->parser = $parser;
        $this->telegramUserService = $telegramUserService;
        $this->telegram = ($telegramFactory)();
        $this->urlBuilder = $urlBuilder;

        $this->tgUser = $tgUser;
        $this->tgLangCode = $tgLangCode;

        $stageContext = new StageContext(
            $this->parser,
            $this->tgUser,
            $this->tgLangCode
        );

        $this->languageStage = new LanguageStage($stageContext);
        $this->genderStage = new GenderStage($stageContext);
    }

    public function getAnswers(
        ?string $text = null,
        ?array $documentInfo = null
    ): StoryMessageSequence
    {
        $stage = $this->getMetaValue(MetaKey::STAGE);

        // check language
        if ($stage === Stage::LANGUAGE) {
            return $this->languageStage->process($text, $this->whereAreWe());
        }

        // check gender
        if ($stage === Stage::GENDER) {
            return $this->genderStage->process($text, $this->whereAreWe());
        }

        // delete story
        if ($stage === Stage::DELETE) {
            $story = $this->getMetaStory();

            if (!$story) {
                return $this->invalidDialogState();
            }

            return $this->processDeleteStory($story, $text);
        }

        // start command
        if ($text === BotCommand::START) {
            return $this->startCommand();
        }

        // check required stages
        $requiredStageMessages = $this->getRequiredStageMessages();

        if (!$requiredStageMessages->isEmpty()) {
            return $requiredStageMessages;
        }

        // story switch command
        if (preg_match("#^/story(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($this->isPlayable($story)) {
                return $this->showStory($story);
            }

            return $this->storyNotFound($storyId);
        }

        // start story from its card
        if (
            $stage === Stage::STORY
            && (
                $text === $this->parse(Action::START_STORY)
                || $text === BotCommand::START_STORY
            )
        ) {
            $story = $this->getMetaStory();

            if (!$story) {
                return $this->invalidDialogState();
            }

            return $this->tryStartStory($story);
        }

        // story edit command
        if (preg_match("#^/edit(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($this->isEditable($story)) {
                return $this->editStoryLink($story);
            }

            return $this->storyNotFound($storyId);
        }

        // story delete command
        if (preg_match("#^/delete(?:\s+|_)(\d+)$#i", $text, $matches)) {
            $storyId = (int)$matches[1];
            $story = $this->getStory($storyId);

            if ($this->isDeletable($story)) {
                return $this->enterDeleteStory($story);
            }

            return $this->storyNotFound($storyId);
        }

        // story language command
        if (preg_match("#^/story_lang(?:\s+|_)(\w+)$#i", $text, $matches)) {
            $langCode = $matches[1];
            return $this->storySelection($langCode);
        }

        // translate the action label here
        if (
            $text === $this->parse(Action::STORY_SELECTION)
            || $text === BotCommand::STORY
        ) {
            return $this->storySelection();
        }

        if ($text === BotCommand::EDIT) {
            return $this->storyEditing();
        }

        if ($text === BotCommand::NEW) {
            return $this->storyCreation();
        }

        if ($text === BotCommand::UPLOAD) {
            return Messages::storyUpload();
        }

        if ($text === BotCommand::CANCEL_UPLOAD) {
            if (Stage::isUploadStage($stage)) {
                return Messages::uploadCanceled();
            }
        }

        if ($text === BotCommand::LANGUAGE) {
            return $this->languageStage->enter();
        }

        if ($text === BotCommand::GENDER) {
            return $this->genderStage->enter();
        }

        $documentUploaded = !empty($documentInfo);

        if ($stage === Stage::UPLOAD) {
            if (!$documentUploaded) {
                return
                    StoryMessageSequence::textFinalized(
                        '❌ [[Please, upload a document.]]',
                        Messages::uploadTips()
                    )
                    ->withStage(Stage::UPLOAD); // we are still on this stage
            }

            return $this->processUpload($documentInfo);
        }

        if ($stage === Stage::EXISTING_STORY || $stage === Stage::NOT_ALLOWED_STORY) {
            return $this->processOverwrite($stage, $text);
        }

        if ($stage === Stage::STORY_LANGUAGE) {
            return $this->processStoryLanguage($text);
        }

        if (strlen($text) === 0) {
            if ($documentUploaded) {
                return
                    StoryMessageSequence::textFinalized(
                        '❌ [[You\'ve uploaded a document, but in a wrong place.]]',
                        '[[If you want to upload a story, use the {{upload_command}} command.]]'
                    )
                    ->withVar('upload_command', BotCommand::UPLOAD);
            }

            return StoryMessageSequence::textFinalized(
                '❌ [[I understand only messages with text.]]'
            );
        }

        // we checked everything unrelated to the current story
        // let's check if there IS a current story

        // is there a status?
        $status = $this->getStatus();

        if (!$status) {
            return $this->cluelessMessage();
        }

        // is the status valid?
        $validationMessages = $this->storyService->validateStatus($status);

        if (!$validationMessages->isEmpty()) {
            return $validationMessages;
        }

        // there is a current story + a valid status
        $story = $this->getStatusStory($status);

        if ($text === $this->parse(Action::RESTART)) {
            return $this->tryStartStory($story);
        }

        if ($text === $this->parse(Action::SHOW_STORY)) {
            return $this->showStory($story);
        }

        // try executing story-specific commands
        if (Strings::startsWith($text, '/')) {
            $executionResults = $story->executeCommand($text);

            if (!$executionResults->isEmpty()) {
                return $executionResults->merge(
                    $this->whereAreWe()
                );
            }
        }

        $sequence = $this->continueStory($story, $status, $text);

        return $sequence->or(
            $this->cluelessMessage()
        );
    }

    private function invalidDialogState(): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage(Messages::INVALID_DIALOG_STATE),
            $this->whereAreWe()
        );
    }

    private function cluelessMessage(): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage(Messages::CLUELESS),
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

        // new player - go to the story selection
        if (!$status) {
            return $this->storySelection();
        }

        // already played - validate the status
        $validationMessages = $this->storyService->validateStatus($status);

        if (!$validationMessages->isEmpty()) {
            return $validationMessages;
        }

        // status is fine here
        $story = $this->getStatusStory($status);

        // the story is already finished, just return an empty sequence
        if ($story->isFinish($status)) {
            return StoryMessageSequence::makeFinalized();
        }

        // re-render the status
        return $story->renderStatus($status);
    }

    private function storyNotFound(int $storyId): StoryMessageSequence
    {
        return
            StoryMessageSequence::mash(
                new TextMessage('❌ [[Story with id = {{story_id}} not found.]]'),
                $this->whereAreWe()
            )
            ->withVar('story_id', $storyId);
    }

    private function startCommand(): StoryMessageSequence
    {
        $greeting = $this->isNewPlayer()
            ? '[[Welcome, <b>{{user_name}}</b>!]]'
            : '[[Welcome back, <b>{{user_name}}</b>!]]';

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
            Stage::LANGUAGE => [
                'check' => fn () => $this->tgUser->hasLanguageCode(),
                'messages' => fn () => $this->languageStage->enter(),
            ],
            Stage::GENDER => [
                'check' => fn () => $this->tgUser->hasGender(),
                'messages' => fn () => $this->genderStage->enter(),
            ],
        ];

        foreach ($stages as $stage) {
            if (!($stage['check'])()) {
                return ($stage['messages'])();
            }
        }

        return StoryMessageSequence::empty();
    }

    private function continueStory(
        Story $story,
        StoryStatus $status,
        string $text
    ): StoryMessageSequence
    {
        if ($story->isFinish($status)) {
            return StoryMessageSequence::empty();
        }

        $sequence = $story->continue($this->tgUser, $status, $text);

        if (!$sequence->isEmpty()) {
            $status->stepId = $sequence->nodeId();
            $status->jsonData = json_encode($sequence->data());

            $this->storyStatusRepository->save($status);
        }

        return $sequence;
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
            return StoryMessageSequence::textFinalized("⛔ [[No stories in {{language}}.]]")
                ->withVar('language', $curLang);
        }

        $lines = $curLangStories->stringize(
            fn (Story $story) => Join::space(
                $story->title(),
                BotCommand::story($story),
                $this->isAdmin() && $this->storyService->isStoryPublic($story) ? '👁' : null,
                $this->isEditable($story) ? '🖊' : null
            )
        );

        $sequence =
            StoryMessageSequence::text(
                "[[Select a story in {$curLang}]]:",
                ...Format::indexLines($lines)
            )
            ->withVar('language', $curLang);

        $groupInfos = ArrayCollection::empty();

        foreach ($groups as $langCode => $group) {
            if ($langCode === $curLangCode) {
                continue;
            }

            $groupInfos = $groupInfos->add([
                'lang_code' => $langCode,
                'language' => Language::fromCode($langCode),
                'stories' => $group,
                'count' => count($group),
            ]);
        }

        if (!empty($groupInfos)) {
            $sequence->addText(
                '[[There are also stories in other languages]]:',
                ...$groupInfos
                    ->sortBy(
                        SortStep::byFuncDesc(
                            fn (array $info) => $info['lang_code'] !== Language::UNKNOWN,
                            Sort::BOOL
                        ),
                        SortStep::byFieldDesc('count')
                    )
                    ->map(
                        function (array $info) {
                            $language = $info['language'];

                            return Join::space(
                                "[[{$language}]]",
                                "({$info['count']})",
                                $language->toCommand()->codeString()
                            );
                        }
                    )
            );
        }

        return $sequence
            ->withActions(Action::STORY_SELECTION)
            ->finalize();
    }

    private function showStory(Story $story): StoryMessageSequence
    {
        $message = new TextMessage(
            Format::bold($story->title()),
            $story->description()
        );

        $cover = $story->cover();

        if ($cover) {
            $message->withImage($cover);
        }

        $meta = [];

        $langCode = $story->languageCode();

        if ($langCode) {
            $meta[] = Join::space(
                '[[Language]]:',
                $this->formatLanguage($langCode)
            );
        }

        $creator = $story->creator();

        if ($creator) {
            $you = $creator->equals(
                $this->tgUser->user()
            );

            $meta[] = Join::space(
                '[[Author]]:',
                Format::bold($creator->displayName()),
                $you ? '([[That\'s you!]])' : null
            );
        }

        if (!empty($meta)) {
            $message->appendLines(Text::join($meta));
        }

        if ($this->isAdmin()) {
            $createdAt = $story->createdAt;

            if ($createdAt) {
                $dates[] = '[[Created at]]: ' . Format::utc($createdAt);
            } else {
                $dates[] = '📌 <i>[[A permanent story]]</i>';
            }

            if ($story->currentVersion()) {
                $updatedAt = $story->currentVersion()->createdAt;
                $dates[] = '[[Updated at]]: ' . Format::utc($updatedAt);
            }

            $message->appendLines(Text::join($dates));
        }

        $message->appendLines(Action::START_STORY . ': ' . BotCommand::START_STORY);

        $sequence = StoryMessageSequence::make($message);

        if ($this->isEditable($story)) {
            $sequence->addText(Action::EDIT . ': ' . BotCommand::edit($story));
            $sequence->addText(Action::UPDATE . ': ' . BotCommand::UPLOAD);
        }

        if ($this->isDeletable($story)) {
            $sequence->addText(Action::DELETE . ': ' . BotCommand::delete($story));
        }

        return $sequence
            ->withActions(Action::START_STORY, Action::STORY_SELECTION)
            ->withStage(Stage::STORY)
            ->withStory($story);
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
                fn (Story $story) => Join::space(
                    $story->title(),
                    BotCommand::edit($story)
                )
            );

            $sequence->addText(
                '[[You can edit the following stories]]:',
                ...Format::indexLines($lines)
            );
        }

        return $sequence->addText(
            '[[Create a new story]]: ' . BotCommand::NEW,
            '[[Upload a new or an edited story]]: ' . BotCommand::UPLOAD
        );
    }

    private function editStoryLink(Story $story): StoryMessageSequence
    {
        $langCode = $this->getLanguageCode();
        $url = $this->urlBuilder->buildStoryEditUrl($story, $langCode);

        return
            StoryMessageSequence::textFinalized(
                '[[Follow the link to edit the story <b>{{story_title}}</b>]]:',
                $url,
                Messages::editorTips()
            )
            ->withVars([
                'story_title' => $story->title(),
                'upload_command' => BotCommand::UPLOAD,
            ]);
    }

    private function storyCreation(): StoryMessageSequence
    {
        $langCode = $this->getLanguageCode();
        $url = $this->urlBuilder->buildStoryCreationUrl($langCode);

        return
            StoryMessageSequence::textFinalized(
                '[[Follow the link to create a new story]]:',
                $url,
                Messages::editorTips()
            )
            ->withVar('upload_command', BotCommand::UPLOAD);
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
                        '[[The file size cannot exceed {{max_json_size}} MB. Upload a smaller file, please.]]',
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

            // 6. get the story id, check that it's a valid uuid (!!!)
            $storyUuid = $jsonData['id'];

            if (!Uuid::isValid($storyUuid)) {
                throw new Exception('[[Story id must be a valid uuid4.]]');
            }

            // 7. check that a story created from JSON passes validation
            $this->storyService->makeStoryFromJson($json);

            // 8. store the JSON to the user's story candidate record
            $storyCandidate = $this->storyService->saveStoryCandidate($this->tgUser, $jsonData);

            // 9. get the story by uuid
            $story = $this->storyService->getStoryByUuid($storyUuid);

            // 10. if the story not found, create a new story
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            // 11. if the story exists, check that the current user can update the story
            $canUpdate = $this->isEditable($story);

            // 12. if they can update it, ask them if they want to create a new version or create a new story (mark the original story as a source story)
            if ($canUpdate) {
                return
                    StoryMessageSequence::text(
                        '⚠ [[The story <b>{{story_title}}</b> already exists.]]',
                        '[[Would you like to update it or to create a new one?]]',
                        Messages::uploadTips()
                    )
                    ->withVar('story_title', $story->title())
                    ->withStage(Stage::EXISTING_STORY)
                    ->withActions(Action::UPDATE, Action::NEW, Action::CANCEL);
            }

            // 13. if they can't update it, tell them that access is denied, but they can save it as a new story (mark the original story as a source story)
            return
                StoryMessageSequence::text(
                    '⛔ [[You are trying to upload a story that you don\'t have access to.]]',
                    '[[Strictly speaking, you shouldn\'t do this.]] 🤔',
                    '[[But since we are already here, you can create a new story.]]',
                    '[[Create a new story?]]',
                    Messages::uploadTips()
                )
                ->withStage(Stage::NOT_ALLOWED_STORY)
                ->withActions(Action::NEW, Action::CANCEL);
        } catch (Exception $ex) {
            return 
                StoryMessageSequence::textFinalized(
                    '❌ ' . $ex->getMessage(),
                    Messages::uploadTips()
                )
                ->withStage(Stage::UPLOAD); // yes, we are still on this stage
        }
    }

    private function processOverwrite(string $stage, string $text): StoryMessageSequence
    {
        // action label must be translated here
        if ($text === $this->parse(Action::CANCEL)) {
            $this->storyService->deleteStoryCandidateFor($this->tgUser);

            return Messages::uploadCanceled();
        }

        $storyCandidate = $this->getStoryCandidate();

        if (!$storyCandidate) {
            return $this->failedToLoadStoryCandidate();
        }

        // action label must be translated here
        if (
            $stage === Stage::EXISTING_STORY
            && $text === $this->parse(Action::UPDATE)
        ) {
            $story = $this->storyService->getStoryByUuid($storyCandidate->uuid);

            // if the story not found for some reason...
            if (!$story) {
                return $this->newStory($storyCandidate);
            }

            // we need to update the story language
            // if the story language is not set, we just set it (in the update)
            // otherwise, we need to check if languages match
            // if they don't, the user must choose one of the languages
            $storyLangCode = $story->languageCode();
            $candidateLangCode = $storyCandidate->language();

            if ($storyLangCode && $candidateLangCode && $storyLangCode !== $candidateLangCode) {
                return $this->chooseStoryLanguage(
                    $this->formatLanguage($storyLangCode),
                    $this->formatLanguage($candidateLangCode)
                );
            }

            return $this->updateStory($story, $storyCandidate);
        }

        // action label must be translated here
        if ($text === $this->parse(Action::NEW)) {
            return $this->forkStory($storyCandidate);
        }

        // we stay put
        return
            StoryMessageSequence::text(
                Messages::CLUELESS,
                Messages::uploadTips()
            )
            ->withStage($stage)
            ->withActions(
                $stage === Stage::EXISTING_STORY
                    ? Action::UPDATE
                    : null,
                Action::NEW,
                Action::CANCEL
            );
    }

    private function chooseStoryLanguage(
        string $oldLanguage,
        string $newLanguage
    ): StoryMessageSequence
    {
        return
            StoryMessageSequence::text(
                '⚠ [[The new story language [{{new_language}}] doesn\'t match the previous story language [{{old_language}}].]]',
                '[[Please, choose the story language.]] 👇',
                Messages::uploadTips()
            )
            ->withVars([
                'old_language' => $oldLanguage,
                'new_language' => $newLanguage,
            ])
            ->withStage(Stage::STORY_LANGUAGE)
            ->withActions($oldLanguage, $newLanguage);
    }

    /**
     * Process the story language selection as a part of the story update process.
     */
    private function processStoryLanguage(string $text): StoryMessageSequence
    {
        $storyCandidate = $this->getStoryCandidate();

        if (!$storyCandidate) {
            return $this->failedToLoadStoryCandidate();
        }

        $story = $this->storyService->getStoryByUuid($storyCandidate->uuid);

        // if the story not found for some reason...
        if (!$story) {
            return $this->newStory($storyCandidate);
        }

        $storyLangCode = $story->languageCode();
        $candidateLangCode = $storyCandidate->language();

        $oldLanguage = $this->formatLanguage($storyLangCode);
        $newLanguage = $this->formatLanguage($candidateLangCode);

        if ($text === $oldLanguage) {
            return $this->updateStory($story, $storyCandidate, $storyLangCode);
        }

        if ($text === $newLanguage) {
            return $this->updateStory($story, $storyCandidate);
        }

        return Messages::writtenSomethingWrong(
            $this->chooseStoryLanguage($oldLanguage, $newLanguage)
        );
    }

    /**
     * Updates the story with a candidate and a language override as a new story version.
     *
     * The resulting language code priority (from highest to lowest):
     * - $langCode param
     * - story candidate's language
     * - story language
     *
     * @param string|null $langCode If provided, the story language is updated with it instead of the candidate's language.
     */
    private function updateStory(
        Story $story,
        StoryCandidate $storyCandidate,
        ?string $langCode = null
    ): StoryMessageSequence
    {
        $oldLangCode = $story->languageCode();

        $updatedStory = $this->storyService->updateStory($story, $storyCandidate, $langCode);

        $text = [
            '✅ [[The story <b>{{story_title}}</b> was successfully updated!]]'
        ];

        $vars = [
            'story_title' => $updatedStory->title(),
        ];

        $newLangCode = $updatedStory->languageCode();

        if ($oldLangCode !== $newLangCode) {
            $text[] = '[[The story language was set to [{{language}}].]]';
            $vars['language'] = $this->formatLanguage($newLangCode);
        }

        $text[] = '🕹 [[Play]]: ' . BotCommand::story($updatedStory);

        return StoryMessageSequence::textFinalized(...$text)
            ->withVars($vars);
    }

    private function forkStory(StoryCandidate $storyCandidate): StoryMessageSequence
    {
        return $this->newStory($storyCandidate, true);
    }

    private function newStory(
        StoryCandidate $storyCandidate,
        bool $fork = false
    ): StoryMessageSequence
    {
        $newStory = $this->storyService->newStory($storyCandidate, $fork);

        $uuidMessage = null;

        if ($fork) {
            $uuidMessage = Text::join([
                '[[The story has got a new id]]:',
                $newStory->uuid
            ]);
        }

        return
            StoryMessageSequence::textFinalized(
                '✅ [[A new story <b>{{story_title}}</b> has been successfully created!]]',
                $uuidMessage,
                '🕹 [[Play]]: ' . BotCommand::story($newStory)
            )
            ->withVar('story_title', $newStory->title());
    }

    private function failedToLoadStoryCandidate(): StoryMessageSequence
    {
        $this->logger->error("Story candidate for Telegram user [{$this->tgUser->getId()}] not found.");

        return StoryMessageSequence::textFinalized(
            '❌ [[Upload error. Try again.]]'
        );
    }

    private function enterDeleteStory($story): StoryMessageSequence
    {
        return
            StoryMessageSequence::text(
                '🗑 [[Are you sure you want to delete the story <b>{{story_title}}</b>?]]'
            )
            ->withVar('story_title', $story->title())
            ->withActions(Action::DELETE, Action::CANCEL)
            ->withStage(Stage::DELETE)
            ->withStory($story);
    }

    private function processDeleteStory(Story $story, string $text): StoryMessageSequence
    {
        if ($text === $this->parse(Action::CANCEL)) {
            return StoryMessageSequence::textFinalized(
                '👌 [[Deletion canceled.]]'
            );
        }

        if ($text === $this->parse(Action::DELETE)) {
            $title = $story->title();

            $this->storyService->deleteStory($story);

            return
                StoryMessageSequence::textFinalized(
                    '✅ [[The story <b>{{story_title}}</b> was successfully deleted!]]'
                )
                ->withVar('story_title', $title);
        }

        return Messages::writtenSomethingWrong(
            $this->enterDeleteStory($story)
        );
    }

    private function getStory(int $storyId): ?Story
    {
        $story = $this->storyService->getStory($storyId);

        return $story && !$story->isDeleted()
            ? $story
            : null;
    }

    private function isNewPlayer(): bool
    {
        return !$this->getStatus();
    }

    private function getStatus(): ?StoryStatus
    {
        return $this->storyStatusRepository->getByTelegramUser($this->tgUser);
    }

    private function getStatusStory(StoryStatus $status): Story
    {
        return $this->storyService->getStatusStory($status);
    }

    private function getStoryCandidate(): ?StoryCandidate
    {
        return $this->storyService->getStoryCandidateFor($this->tgUser);
    }

    private function getMetaStory(): ?Story
    {
        $storyId = (int)$this->getMetaValue(MetaKey::STORY_ID);

        if (!$storyId) {
            return null;
        }

        return $this->getStory($storyId);
    }

    private function getMetaValue(string $key)
    {
        return $this->tgUser->getMetaValue($key);
    }

    private function isAdmin(): bool
    {
        return $this->storyService->isAdmin($this->tgUser);
    }

    private function isAdminOrCreator(Story $story): bool
    {
        return $this->storyService->isAdminOrStoryCreator($story, $this->tgUser);
    }

    private function isPlayable(?Story $story): bool
    {
        if (!$story) {
            return false;
        }

        return $this->storyService->isStoryPlayableBy($story, $this->tgUser);
    }

    private function isEditable(?Story $story): bool
    {
        if (!$story) {
            return false;
        }

        return $this->storyService->isStoryEditableBy($story, $this->tgUser);
    }

    private function isDeletable(?Story $story): bool
    {
        if (!$story) {
            return false;
        }

        return $this->storyService->isStoryDeletableBy($story, $this->tgUser);
    }

    private function formatLanguage(string $langCode): string
    {
        if (!Language::isKnown($langCode)) {
            return $langCode;
        }

        return $this->translate(
            Language::fromCode($langCode)->toString()
        );
    }

    private function translate(string $text): string
    {
        return $this->parse("[[{$text}]]");
    }

    private function parse(string $text, ?array $vars = null): string
    {
        return $this->parser->parse($this->tgUser, $text, $vars, $this->tgLangCode);
    }

    private function getLanguageCode(): string
    {
        return $this->tgUser->languageCode() ?? $this->tgLangCode;
    }
}
