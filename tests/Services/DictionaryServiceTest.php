<?php

namespace App\Tests\Services;

use App\Services\DictionaryService;
use App\Tests\BaseTestCase;

final class DictionaryServiceTest extends BaseTestCase
{
    public function testIsWordKnown() : void
    {
        /** @var DictionaryService */
        $service = $this->container->dictionaryService;

        $wordRepository = $this->container->wordRepository;
        $word = $wordRepository->get(1); // стол

        $dictWord = $service->getByWord($word, true);

        $this->assertNotNull($dictWord);
        $this->assertTrue($dictWord->getLinkedWord()->equals($word));
        $this->assertTrue($dictWord->isValid());
        $this->assertTrue($dictWord->isNoun());
    }
}
