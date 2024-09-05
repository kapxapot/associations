<?php

namespace Brightwood\Answers;

class Stage
{
    const GENDER = 'gender';
    const LANGUAGE = 'language';
    const STORY = 'story';
    const UPLOAD = 'upload';
    const EXISTING_STORY = 'existing_story';
    const NOT_ALLOWED_STORY = 'not_allowed_story';
    const STORY_LANGUAGE = 'story_language';
    const DELETE = 'delete';

    public static function isUploadStage(string $stage): bool
    {
        return in_array(
            $stage,
            [
                self::UPLOAD,
                self::EXISTING_STORY,
                self::NOT_ALLOWED_STORY,
                self::STORY_LANGUAGE
            ]
        );
    }
}
