<?php

namespace App\Config;

use Plasticode\Config\Localization as LocalizationBase;

class Localization extends LocalizationBase
{
    protected function ru()
    {
        return array_merge(
            parent::ru(),
            [
                'age' => 'Возраст',
                
                'Language not found.' => 'Язык не найден.',
                'Association not found.' => 'Ассоциация не найдена.',
                'Game not found.' => 'Игра не найдена.',
                'Turn not found.' => 'Ход не найден.',
                'Word not found.' => 'Слово не найдено.',
                'Game is already on.' => 'Игра уже идет.',
                'Provided game is not the current game of the current user.' => 'Указанная игра не является текущей игрой текущего пользователя.',
                'Game turn is not correct (reload the page).' => 'Некорректный ход игры (перезагрузите страницу).',
                'Word must differ from two previous words.' => 'Слово должно отличаться от двух предыдущих.',
                'Word must contain only letters, digits, -, _ and \'.' => 'Слово должно содержать только буквы, цифры, -, _ и \'.',
                'Main word must exist and be different.' => 'Главное слово должно существовать и отличаться от текущего.',
            ]
        );
    }
}
