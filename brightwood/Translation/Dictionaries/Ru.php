<?php

namespace Brightwood\Translation\Dictionaries;

use Brightwood\Translation\Interfaces\DictionaryInterface;

class Ru implements DictionaryInterface
{
    public function definitions(): array
    {
        return [
            'Mb' => 'Мб',
            'Boy' => 'Мальчик',
            'Girl' => 'Девочка',
            'Update' => 'Обновить',
            'Create new' => 'Создать новую',
            'Cancel' => 'Отмена',
            'Huh? I didn\'t get it...' => 'Что-что? Повторите-ка...',
            'Incorrect file type. Upload a JSON exported from the editor, please.' => 'Неверный тип файла. Загрузите JSON, полученный из редактора, пожалуйста.',
            'Failed to get the file from Telegram, try again.' => 'Не удалось получить файл от Telegram, попробуйте еще раз.',
            'Invalid file. Upload a valid JSON file, please.' => 'Файл поврежден. Пожалуйста, загрузите валидный JSON-файл.',
            'Story id must be a valid uuid4.' => 'Id истории должен быть валидным uuid4.',
            'Please, upload a document.' => 'Пожалуйста, загрузите документ.',
            'You\'ve uploaded a document, but in a wrong place.' => 'Вы загрузили документ, но не там, где нужно.',
            'I understand only messages with text.' => 'Я понимаю только сообщения с текстом.',
            'The end' => 'Конец',
            'Start again' => 'Начать заново',
            'Select story' => 'Выбрать историю',
            'The bot is broken! Fix it!' => 'Бот сломался! Почините!',
            'Something went wrong.' => 'Что-то пошло не так.',
            'Story with id = {storyId} not found.' => 'История с id = {storyId} не найдена.',
            'If you want to upload a story, use {upload_command} command' => 'Если вы хотите загрузить историю, используйте команду {upload_command}',
            'Welcome back, <b>{userName}</b>!' => 'С возвращением, <b>{userName}</b>!',
            'Welcome, <b>{userName}</b>!' => 'Добро пожаловать, <b>{userName}</b>!',
        ];
    }
}
