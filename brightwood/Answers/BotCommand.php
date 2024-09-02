<?php

namespace Brightwood\Answers;

use Brightwood\Models\Stories\Core\Story;
use Brightwood\Util\Join;

class BotCommand
{
    const START = '/start';
    const START_STORY = '/start_story';
    const STORY = '/story';
    const EDIT = '/edit';
    const DELETE = '/delete';
    const NEW = '/new';
    const UPLOAD = '/upload';
    const CANCEL_UPLOAD = '/cancel_upload';
    const LANGUAGE = '/language';
    const GENDER = '/gender';

    public static function story(Story $story): string
    {
        return Join::underline(self::STORY, $story->getId());
    }

    public static function edit(Story $story): string
    {
        return Join::underline(self::EDIT, $story->getId());
    }

    public static function delete(Story $story): string
    {
        return Join::underline(self::DELETE, $story->getId());
    }
}
