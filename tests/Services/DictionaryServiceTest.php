<?php

namespace App\Tests\Services;

use App\Models\Language;
use App\Models\Word;
use App\Services\DictionaryService;
use App\Testing\Mocks\Repositories\DictWordRepositoryMock;
use App\Testing\Mocks\Services\ExternalDictServiceMock;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;

final class DictionaryServiceTest extends TestCase
{
    private Language $language;
    private DictionaryService $dictService;

    protected function setUp() : void
    {
        parent::setUp();

        /** @var Language */
        $this->language = Language::create(
            [
                'id' => 1,
                'name' => 'Dummy',
            ]
        );

        $this->dictService = new DictionaryService(
            new DictWordRepositoryMock(),
            new ExternalDictServiceMock(
                new DictWordRepositoryMock()
            ),
            new EventDispatcher()
        );
    }

    protected function tearDown() : void
    {
        unset($this->dictService);
        unset($this->language);

        parent::tearDown();
    }

    public function testGetByWordRemoteValid() : void
    {
        /** @var Word */
        $word = Word::create(
            [
                'id' => 1,
                'language_id' => $this->language->getId(),
                'word' => 'стол',
            ]
        );

        $word = $word->withLanguage($this->language);

        $dictWord = $this->dictService->getByWord($word, true);

        // dictWord and word should be linked
        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word));
        $this->assertTrue($word->dictWord()->equals($dictWord));
        $this->assertTrue($dictWord->isValid());
        $this->assertTrue($dictWord->isGood());
    }

    public function testGetByWordRemoteInvalid() : void
    {
        /** @var Word */
        $word = Word::create(
            [
                'id' => 2,
                'language_id' => $this->language->getId(),
                'word' => 'табурет',
            ]
        );

        $word = $word->withLanguage($this->language);

        $dictWord = $this->dictService->getByWord($word, true);

        // dictWord and word should be linked
        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word));
        $this->assertTrue($word->dictWord()->equals($dictWord));
        $this->assertFalse($dictWord->isValid());
        $this->assertFalse($dictWord->isGood());
    }

    public function testRelink() : void
    {
        /** @var Word */
        $word1 = Word::create(
            [
                'id' => 1,
                'language_id' => $this->language->getId(),
                'word' => 'стол',
            ]
        );

        /** @var Word */
        $word2 = Word::create(
            [
                'id' => 2,
                'language_id' => $this->language->getId(),
                'word' => 'кровать',
            ]
        );

        $word1 = $word1->withLanguage($this->language);
        $word2 = $word2->withLanguage($this->language);

        $dictWord = $this->dictService->getByWord($word1, true);

        // dictWord and word1 should be linked
        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word1));
        $this->assertTrue($word1->dictWord()->equals($dictWord));

        $dictWord = $this->dictService->link($dictWord, $word2);

        // dictWord and word2 should be linked
        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word2));
        $this->assertTrue($word2->dictWord()->equals($dictWord));
        $this->assertNull($word1->dictWord());
    }

    public function testUnlink() : void
    {
        /** @var Word */
        $word = Word::create(
            [
                'id' => 1,
                'language_id' => $this->language->getId(),
                'word' => 'стол',
            ]
        );

        $word = $word->withLanguage($this->language);

        $dictWord = $this->dictService->getByWord($word, true);

        // dictWord and word should be linked
        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word));
        $this->assertTrue($word->dictWord()->equals($dictWord));

        $dictWord = $this->dictService->unlink($dictWord);

        // dictWord and word should be not linked
        $this->assertNotNull($dictWord);
        $this->assertNull($dictWord->getLinkedWord());
        $this->assertNull($word->dictWord());
    }
}
