<?php

namespace App\Config;

use Plasticode\Config\Localization as LocalizationBase;

class Localization extends LocalizationBase
{
    protected function ru() : array
    {
        return array_merge(
            parent::ru(),
            [
                'age' => 'Возраст',
                
                'Language not found.' => 'Язык не найден.',
                'New game started.' => 'Начата новая игра.',
                'Association not found.' => 'Ассоциация не найдена.',
                'Game not found.' => 'Игра не найдена.',
                'Turn not found.' => 'Ход не найден.',
                'Word not found.' => 'Слово не найдено.',
                'Game is already on.' => 'Игра уже идет.',
                'Game is finished. Please, reload the page.' => 'Игра уже завершена. Пожалуйста, обновите страницу.',
                'Game turn is not correct. Please, reload the page.' => 'Некорректный ход игры. Пожалуйста, обновите страницу.',
                'Word is already used in this game.' => 'Слово уже использовано в этой игре.',
                'Word must contain only letters, digits, -, _ and \'.' => 'Слово должно содержать только буквы, цифры, -, _ и \'.',
                'Main word must be different and present in game.' => 'Главное слово должно присутствовать в игре и отличаться от текущего.',
            ]
        );
    }
}
