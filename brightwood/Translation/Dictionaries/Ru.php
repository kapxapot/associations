<?php

namespace Brightwood\Translation\Dictionaries;

use Brightwood\Translation\Interfaces\DictionaryInterface;

class Ru implements DictionaryInterface
{
    const LANG_CODE = 'ru';
    const LANG_NAME = 'Русский';

    public function languageCode(): string
    {
        return self::LANG_CODE;
    }

    public function languageName(): string
    {
        return self::LANG_NAME;
    }

    /**
     * @return array<string, string>
     */
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
        ];
    }
}
