<?php

namespace App\Tests\External;

use App\External\DictionaryApi;
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Testing\Mocks\Repositories\LanguageRepositoryMock;
use App\Testing\Seeders\LanguageSeeder;
use App\Tests\IntegrationTest;
use Plasticode\Core\Factories\ConsoleLoggerFactory;

final class DictionaryApiTest extends IntegrationTest
{
    private LanguageRepositoryInterface $languageRepository;
    private Language $language;
    private DictionaryApi $dictApi;

    public function setUp(): void
    {
        parent::setUp();

        $this->languageRepository = new LanguageRepositoryMock(
            new LanguageSeeder()
        );

        $this->language = $this->languageRepository->get(Language::RUSSIAN);

        $this->dictApi = new DictionaryApi(
            (new ConsoleLoggerFactory())()
        );
    }

    public function tearDown(): void
    {
        unset($this->dictApi);
        unset($this->language);
        unset($this->languageRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider existingWordsProvider
     */
    public function testExistingWords(string $word): void
    {
        //$this->markTestSkipped('Api is unstable');

        $result = $this->dictApi->request($this->language->code, $word);

        $this->assertNotNull($result);

        $data = json_decode($result->jsonData(), true);

        $this->assertIsArray($data);

        $this->assertNotEmpty(
            $data[0]['word']
        );
    }

    public function existingWordsProvider(): array
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
        //$this->markTestSkipped('Api is unstable');

        $result = $this->dictApi->request($this->language->code, $word);

        $this->assertNotNull($result);

        $data = json_decode($result->jsonData(), true);

        $this->assertIsArray($data);

        $this->assertNull($data[0] ?? null);
    }

    public function notExistingWordsProvider(): array
    {
        return [
            ['чучундрик'],
            ['ыавлорап'],
            ['лоавпавп'],
        ];
    }
}
