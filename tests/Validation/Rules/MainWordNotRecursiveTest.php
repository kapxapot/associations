<?php

namespace App\Tests\Validation\Rules;

use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use App\Testing\Seeders\LanguageSeeder;
use App\Tests\WiredTest;
use App\Validation\Rules\MainWordNotRecursive;

final class MainWordNotRecursiveTest extends WiredTest
{
    private Language $language;

    private Word $word1;
    private Word $word2;
    private Word $word3;
    private Word $mainWord;

    public function setUp(): void
    {
        parent::setUp();

        /** @var LanguageRepositoryInterface $languageRepository */
        $languageRepository = $this->get(LanguageRepositoryInterface::class);

        /** @var WordRepositoryInterface $wordRepository */
        $wordRepository = $this->get(WordRepositoryInterface::class);

        $this->language = $languageRepository->get(LanguageSeeder::RUSSIAN);

        $this->word1 = $wordRepository->store(['word' => 'word1']);
        $this->word1->withLanguage($this->language);

        $this->word2 = $wordRepository->store(['word' => 'word2']);
        $this->word2->withLanguage($this->language);

        $this->word3 = $wordRepository->store(['word' => 'word3']);
        $this->word3->withLanguage($this->language);

        $this->mainWord = $wordRepository->store(['word' => 'main word']);
        $this->mainWord->withLanguage($this->language);

        $this->word1->withMain($this->word2);
        $this->word2->withMain($this->word3);
        $this->word3->withMain(null);
        $this->mainWord->withMain(null);
    }

    public function tearDown(): void
    {
        unset($this->mainWord);
        unset($this->word2);
        unset($this->word1);

        unset($this->language);

        parent::tearDown();
    }

    /** @dataProvider notRecursiveProvider */
    public function testNotRecursive(
        string $dependentWordStr,
        ?string $mainWordStr,
        bool $expected
    ): void
    {
        /** @var LanguageService $languageService */
        $languageService = $this->get(LanguageService::class);

        $dependentWord = $languageService->findWord(
            $this->language,
            $dependentWordStr
        );

        $rule = new MainWordNotRecursive($languageService, $dependentWord);

        $this->assertEquals($expected, $rule->validate($mainWordStr));
    }

    public function notRecursiveProvider(): array
    {
        // ok: word1 +> null
        // ok: word1 +> mainWord
        // ok: word1 -> word2 +> mainWord
        // not ok: word1 +> word1
        // not ok: word1 -> word2 +> word1
        // not ok: word1 -> word2 -> word3 +> word1

        return [
            ['word1', null, true],
            ['word1', 'main word', true],
            ['word2', 'main word', true],
            ['word1', 'word1', false],
            ['word2', 'word1', false],
            ['word3', 'word1', false],
        ];
    }
}
