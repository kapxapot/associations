<?php

namespace Brightwood\Models;

use Brightwood\Models\Stories\Core\Story;
use Brightwood\Util\Join;

class BotCommand
{
    const START_STORY = '🚀 [[Start]]';
    const RESTART = '♻ [[Start again]]';
    const STORY_SELECTION = '📚 [[Select story]]';
    const TROUBLESHOOT = '[[The bot is broken! Fix it!]]';

    const CODE_START = '/start';
    const CODE_START_STORY = '/start_story';
    const CODE_STORY = '/story';
    const CODE_EDIT = '/edit';
    const CODE_NEW = '/new';
    const CODE_UPLOAD = '/upload';
    const CODE_CANCEL_UPLOAD = '/cancel_upload';
    const CODE_LANGUAGE = '/language';
    const CODE_GENDER = '/gender';

    public static function story(Story $story): string
    {
        return Join::underline(self::CODE_STORY, $story->getId());
    }

    public static function edit(Story $story): string
    {
        return Join::underline(self::CODE_EDIT, $story->getId());
    }
}
