<?php

namespace Brightwood\Answers;

use Brightwood\Models\Messages\StoryMessageSequence;

class Stage
{
    const GENDER = 'gender';
    const LANGUAGE = 'language';
    const STORY = 'story';
    const UPLOAD = 'upload';
    const EXISTING_STORY = 'existing_story';
    const NOT_ALLOWED_STORY = 'not_allowed_story';
    const DELETE = 'delete';

    /**
     * @return string[]
     */
    public static function uploadStages(): array
    {
        return [
            self::UPLOAD,
            self::EXISTING_STORY,
            self::NOT_ALLOWED_STORY
        ];
    }

    public static function isUploadStage(string $stage): bool
    {
        return in_array($stage, self::uploadStages());
    }

    public static function setStage(
        StoryMessageSequence $sequence,
        string $stage
    ): StoryMessageSequence
    {
        $sequence->withStage($stage);

        if ($stage === Stage::EXISTING_STORY) {
            return $sequence->withActions(
                Action::UPDATE,
                Action::NEW,
                Action::CANCEL
            );
        }

        if ($stage === Stage::NOT_ALLOWED_STORY) {
            return $sequence->withActions(
                Action::NEW,
                Action::CANCEL
            );
        }

        return $sequence;
    }
}
