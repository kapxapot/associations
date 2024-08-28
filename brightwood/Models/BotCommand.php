<?php

namespace Brightwood\Models;

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
}
