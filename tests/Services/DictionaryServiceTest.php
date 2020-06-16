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
        $this->assertTrue($dictWord->isNoun());
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
        $this->assertFalse($dictWord->isNoun());
    }
}
