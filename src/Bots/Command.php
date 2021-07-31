<?php

namespace App\Bots;

use Plasticode\Util\Strings;

class Command
{
    const ASSOCIATION_DISLIKE = 'плохая ассоциация';
    const COMMAND = 'команда';
    const COMMANDS = 'команды';
    const ENOUGH = 'хватит';
    const HELP = 'помощь';
    const PLAY = 'играть';
    const PLAYING = 'играем';
    const REPEAT = 'повтори';
    const RULES = 'правила';
    const SKIP = 'дальше';
    const WHAT = 'что это';
    const WORD_DISLIKE = 'плохое слово';

    public static function getLabel(string $command): string
    {
        $labels = [
            self::WHAT => 'что это?',
        ];

        $label = $labels[$command] ?? $command;

        return Strings::upperCaseFirst($label);
    }
}
