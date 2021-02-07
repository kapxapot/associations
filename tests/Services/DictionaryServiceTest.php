<?php

namespace App\Tests\Services;

use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Repositories\DictWordRepositoryMock;
use App\Testing\Mocks\Repositories\LanguageRepositoryMock;
use App\Testing\Mocks\Services\ExternalDictServiceMock;
use App\Testing\Seeders\LanguageSeeder;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;

final class DictionaryServiceTest extends TestCase
{
    private WordRepositoryInterface $wordRepository;
    private DictionaryService $dictService;

    protected function setUp() : void
    {
        parent::setUp();

        $this->wordRepository = WordRepositoryFactory::make();

        $languageRepository = new LanguageRepositoryMock(
            new LanguageSeeder()
        );

        $dictWordRepository = new DictWordRepositoryMock(
            $languageRepository
        );

        $this->dictService = new DictionaryService(
            $dictWordRepository,
            new ExternalDictServiceMock(
                $dictWordRepository
            ),
            new EventDispatcher()
        );
    }

    protected function tearDown() : void
    {
        unset($this->dictService);
        unset($this->wordRepository);

        parent::tearDown();
    }

    public function testGetByWordRemoteValid() : void
    {
        $word = $this->wordRepository->get(1);

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
        $word = $this->wordRepository->get(2);

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
        $word1 = $this->wordRepository->get(1);
        $word2 = $this->wordRepository->get(3);

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
        $word = $this->wordRepository->get(1);

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
