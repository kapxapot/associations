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
                'Provided game is finished. Please, reload the page.' => 'Указанная игра уже завершена. Пожалуйста, обновите страницу.',
                'Game turn is not correct. Please, reload the page.' => 'Некорректный ход игры. Пожалуйста, обновите страницу.',
                'Word is already used in this game.' => 'Слово уже использовано в этой игре.',
                'Word must contain only letters, digits, -, _ and \'.' => 'Слово должно содержать только буквы, цифры, -, _ и \'.',
                'Main word must exist and be different.' => 'Главное слово должно существовать и отличаться от текущего.',
            ]
        );
    }
}
