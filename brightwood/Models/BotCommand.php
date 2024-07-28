<?php

namespace Brightwood\Models;

class BotCommand
{
    const RESTART = '♻ Начать заново';
    const STORY_SELECTION = '📚 Выбрать историю';

    const CODE_START = '/start';
    const CODE_STORY = '/story';
    const CODE_EDIT = '/edit';
    const CODE_NEW = '/new';
}
