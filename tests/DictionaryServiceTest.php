<?php

namespace App\Tests;

use App\Models\Language;
use App\Models\Word;

final class DictionaryServiceTest extends BaseTestCase
{
    /** @dataProvider isWordStrKnownProvider */
    public function testIsWordStrKnown(string $word, bool $expected) : void
    {
        $languageRepository = $this->container->languageRepository;
        $language = $languageRepository->get(Language::RUSSIAN);

        $service = $this->container->dictionaryService;
        
        $actual = $service->isWordStrKnown($language, $word);

        $this->assertEquals($expected, $actual);
    }

    public function isWordStrKnownProvider()
    {
        return [
            ['секс', true],
            ['самолет', true],
            ['таблица', true],
            ['чучундрик', false],
            ['овоывалоарл', false],
        ];
    }

    /** @dataProvider isWordKnownProvider */
    public function testIsWordKnown(int $wordId, bool $expected) : void
    {
        $service = $this->container->dictionaryService;

        $wordRepository = $this->container->wordRepository;
        $word = $wordRepository->get($wordId);

        $actual = $service->isWordKnown($word);

        $this->assertEquals($expected, $actual);
    }

    public function isWordKnownProvider()
    {
        return [
            [1, true],
        ];
    }
}
