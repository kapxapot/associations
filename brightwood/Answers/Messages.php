<?php

namespace Brightwood\Answers;

use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use Plasticode\Util\Text;

class Messages
{
    const CLUELESS = '[[Huh? I didn\'t get it...]] 🧐';
    const INVALID_DIALOG_STATE = '⛔ [[Invalid dialog state.]]';

    public static function writtenSomethingWrong(?StoryMessageSequence $sequence = null): StoryMessageSequence
    {
        return StoryMessageSequence::mash(
            new TextMessage('[[You\'ve written something wrong.]] 🤔'),
            $sequence
        );
    }

    public static function invalidStoryState(?StoryMessageSequence $preSequence = null): StoryMessageSequence
    {
        $msg = '⛔ [[Invalid story state. Please, start again or select another story.]]';

        if ($preSequence) {
            return $preSequence->addText($msg)->stuck();
        }

        return StoryMessageSequence::textStuck($msg);
    }

    public static function uploadCanceled(): StoryMessageSequence
    {
        return StoryMessageSequence::textFinalized('✅ [[Story upload canceled.]]');
    }

    public static function uploadTips(): string
    {
        return '[[Cancel the upload]]: ' . BotCommand::CANCEL_UPLOAD;
    }

    /**
     * Add {upload_command} var.
     */
    public static function editorTips(): string
    {
        return Text::join([
            '🔹 ⚠ [[At the moment, the editor works correctly only on a <b>desktop</b>!]]',
            '🔹 [[After editing the story export it into a JSON file and upload it here, using the {upload_command} command.]]'
        ]);
    }

    public static function storyUpload(): StoryMessageSequence
    {
        return
            StoryMessageSequence::textFinalized(
                '[[Upload the story JSON file exported from the editor.]] 👇',
                Messages::uploadTips()
            )
            ->withStage(Stage::UPLOAD);
    }
}
