<?php

namespace App\Tests\External;

use App\External\YandexDict;
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Testing\Mocks\Repositories\LanguageRepositoryMock;
use App\Testing\Seeders\LanguageSeeder;
use App\Tests\IntegrationTest;
use Plasticode\Settings\SettingsProvider;

final class YandexDictTest extends IntegrationTest
{
    private LanguageRepositoryInterface $languageRepository;
    private Language $language;
    private YandexDict $dict;

    public function setUp(): void
    {
        parent::setUp();

        $this->languageRepository = new LanguageRepositoryMock(
            new LanguageSeeder()
        );

        $this->language = $this->languageRepository->get(LanguageSeeder::RUSSIAN);

        $this->dict = new YandexDict(
            new SettingsProvider($this->settings)
        );
    }

    public function tearDown(): void
    {
        unset($this->dict);
        unset($this->language);
        unset($this->languageRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider existingWordsProvider
     */
    public function testExistingWords(string $word): void
    {
        $result = $this->dict->request($this->language->yandexDictCode, $word);

        $data = json_decode($result, true);

        $def = $data['def'][0] ?? null;

        $text = $def['text'] ?? null;
        $pos = $def['pos'] ?? null;

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

    /**
     * @dataProvider notExistingWordsProvider
     */
    public function testNotExistingWords(string $word): void
    {
        $result = $this->dict->request($this->language->yandexDictCode, $word);

        $data = json_decode($result, true);

        $def = $data['def'][0] ?? null;

        $text = $def['text'] ?? null;
        $pos = $def['pos'] ?? null;

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
