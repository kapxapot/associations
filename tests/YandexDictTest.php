<?php

namespace App\Tests;

use App\Models\Language;

final class YandexDictTest extends BaseTestCase
{
    /** @dataProvider existingWordsProvider */
    public function testExistingWords(string $word): void
    {
        $dict = $this->container->yandexDict;
        $language = Language::get(Language::RUSSIAN);
        
        $result = $dict->request($language->yandexDictCode, $word);
        
        $data = json_decode($result, true);

        $def = $data['def'][0] ?? null;

        if ($def) {
            $text = $def['text'] ?? null;
            $pos = $def['pos'] ?? null;
        }

        $this->assertEquals($word, $text);
        $this->assertNotNull($pos);
    }

    public function existingWordsProvider()
    {
        return [
            ['секс'],
            ['самолет'],
            ['таблица'],
        ];
    }
    /** @dataProvider notExistingWordsProvider */
    public function testNotExistingWords(string $word): void
    {
        $dict = $this->container->yandexDict;
        $language = Language::get(Language::RUSSIAN);
        
        $result = $dict->request($language->yandexDictCode, $word);
        
        $data = json_decode($result, true);

        $def = $data['def'][0] ?? null;

        if ($def) {
            $text = $def['text'] ?? null;
            $pos = $def['pos'] ?? null;
        }

        $this->assertNull($text);
        $this->assertNull($pos);
    }

    public function notExistingWordsProvider()
    {
        return [
            ['чучундрик'],
            ['ыавлорап'],
            ['лоавпавп'],
        ];
    }
}
