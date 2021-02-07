<?php

namespace App\Testing\Mocks\External;

use App\External\Interfaces\DefinitionSourceInterface;
use App\Models\DTO\DefinitionData;

class DefinitionSourceMock implements DefinitionSourceInterface
{
    public function request(string $languageCode, string $word): DefinitionData
    {
        if ($word === 'стол') {
            return new DefinitionData(
                'mock',
                'dummyUrl/' . $word,
                '[{"word": "СТОЛ", "meanings": [{"partOfSpeech": "Мужской род", "definitions": [{"definition": "Предмет мебели в виде широкой горизонтальной доски на высоких опорах, ножках.", "example": "Обедать за столом"}, {"definition": "Питание, пища.", "example": "Снять комнату со столом"}, {"definition": "Отделение в учреждении, ведающее каким-н. специальным кругом дел.", "example": "Справочный с."}]}]}]'
            );
        }

        return new DefinitionData(
            'mock',
            'dummyUrl/' . $word,
            null
        );
    }
}
