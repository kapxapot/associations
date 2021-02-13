<?php

namespace App\Testing\Mocks\External;

use App\External\DictionaryApi;
use App\External\Interfaces\DefinitionSourceInterface;
use App\Models\DTO\DefinitionData;

class DefinitionSourceMock implements DefinitionSourceInterface
{
    public function request(string $languageCode, string $word): DefinitionData
    {
        if ($word === 'стол') {
            return new DefinitionData(
                DictionaryApi::SOURCE,
                'dummyUrl/' . $word,
                '[{"word": "СТОЛ", "meanings": [{"partOfSpeech": "Мужской род", "definitions": [{"definition": "Предмет мебели в виде широкой горизонтальной доски на высоких опорах, ножках.", "example": "Обедать за столом"}, {"definition": "Питание, пища.", "example": "Снять комнату со столом"}, {"definition": "Отделение в учреждении, ведающее каким-н. специальным кругом дел.", "example": "Справочный с."}]}]}]'
            );
        }

        return new DefinitionData(
            DictionaryApi::SOURCE,
            'dummyUrl/' . $word,
            '{"title":"No Definitions Found","message":"Sorry pal, we couldn\'t find definitions for the word you were looking for.","resolution":"You can try the search again at later time or head to the web instead."}'
        );
    }
}
