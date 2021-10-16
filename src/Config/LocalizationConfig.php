<?php

namespace App\Config;

use Plasticode\Config\LocalizationConfig as BaseConfig;

class LocalizationConfig extends BaseConfig
{
    protected function ru(): array
    {
        return array_merge(
            parent::ru(),
            [
                'age' => 'Возраст',

                'noun' => 'существительное',
                'adjective' => 'прилагательное',
                'pronoun' => 'местоимение',
                'numeral' => 'числительное',
                'verb' => 'глагол',
                'adverb' => 'наречие',
                'participle' => 'причастие',
                'adverbial participle' => 'деепричастие',
                'preposition' => 'предлог',
                'conjunction' => 'союз',
                'predicative' => 'частица',
                'interjection' => 'междометие',

                'n.' => 'сущ.',
                'adj.' => 'прил.',
                'pron.' => 'местоим.',
                'num.' => 'числ.',
                'v.' => 'гл.',
                'adv.' => 'нареч.',
                'part.' => 'прич.',
                'adv. part.' => 'дееприч.',
                'prep.' => 'предл.',
                'conj.' => 'союз',
                'pred.' => 'част.',
                'interj.' => 'межд.',

                'Plural form' => 'Множественное число',
                'Typo' => 'Опечатка',
                'Alternative form' => 'Альтернативная форма',
                'Grammatical form' => 'Грамматическая форма',
                'Diminutive form' => 'Уменьшительно-ласкательное',
                'Gender form' => 'Родовая форма',
                'Homophone' => 'Омофон',
                'Phonetic typo' => 'Фонетическая опечатка',
                'Translation' => 'Перевод',
                'Localization' => 'Локализация',
                'Duplicate' => 'Дубль',
                'Prepositional phrase' => 'Предложная фраза',
                'Acronym' => 'Сокращение',
                'Augmentative form' => 'Увеличительное',

                'Word:disabled' => 'отключено',
                'Word:inactive' => 'неактивное',
                'Word:private' => 'личное',
                'Word:public' => 'публичное',
                'Word:common' => 'общее',

                'Word:neutral' => 'нейтральное',
                'Word:offending' => 'неприятное',
                'Word:mature' => 'для взрослых',

                'Association:disabled' => 'отключена',
                'Association:inactive' => 'неактивная',
                'Association:private' => 'личная',
                'Association:public' => 'публичная',
                'Association:common' => 'общая',

                'Association:neutral' => 'нейтральная',
                'Association:offending' => 'неприятная',
                'Association:mature' => 'для взрослых',

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
                'Feedback saved successfully.' => 'Ваш отзыв успешно сохранен.',

                'No words yet. :(' => 'Пока слов нет. :(',
                'No associations yet. :(' => 'Пока ассоциаций нет. :(',

                'Typo must differ from the word.' => 'Опечатка должна отличаться от слова.',
                'Word correction must differ from the word.' => 'Исправление должно отличаться от слова.',
                'Such word already exists.' => 'Такое слово уже существует.',

                'Are you sure you want to delete the relation?' => 'Действительно удалить связь?',
                'Relation deleted successfully.' => 'Связь успешно удалена.',
                'Failed to load word relations.' => 'Не удалось загрузить связи слова.',

                'Word "%s" is already used in the game.' => 'Слово «%s» уже использовано в игре.',
                'Related word "%s" is already used in the game.' => 'Связанное слово «%s» уже использовано в игре.',
                'Related word "%s" is recently used in the game.' => 'Связанное слово «%s» недавно использовано в игре.',

                'Main word creates recursion.' => 'Главное слово создает рекурсию.',
            ]
        );
    }
}
