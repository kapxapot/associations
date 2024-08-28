<?php

namespace Brightwood\Models;

use Brightwood\Models\Stories\Core\Story;

class BotCommand
{
    const RESTART = '♻ [[Start again]]';
    const STORY_SELECTION = '📚 [[Select story]]';
    const TROUBLESHOOT = '[[The bot is broken! Fix it!]]';

    const CODE_START = '/start';
    const CODE_STORY = '/story';
    const CODE_EDIT = '/edit';
    const CODE_NEW = '/new';
    const CODE_UPLOAD = '/upload';
    const CODE_CANCEL_UPLOAD = '/cancel_upload';

    public static function story(Story $story): string
    {
        return sprintf('%s_%s', self::CODE_STORY, $story->getId());
    }

    public static function edit(Story $story): string
    {
        return sprintf('%s_%s', self::CODE_EDIT, $story->getId());
    }
}
