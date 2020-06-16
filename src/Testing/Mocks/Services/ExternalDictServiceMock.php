<?php

namespace App\Testing\Mocks\Services;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;

class ExternalDictServiceMock implements ExternalDictServiceInterface
{
    private DictWordRepositoryInterface $dictWordRepository;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository
    )
    {
        $this->dictWordRepository = $dictWordRepository;
    }

    public function loadFromDictionary(
        Language $language,
        string $wordStr
    ) : ?DictWordInterface
    {
        if ($wordStr == 'стол') {
            $dictWord = $this->dictWordRepository->create(
                [
                    'word' => $wordStr,
                    'language_id' => $language->getId(),
                    'response' => '{"head":{},"def":[{"text":"стол","pos":"noun"}]}',
                    'pos' => 'noun',
                ]
            );

            return $dictWord;
        }

        return $this->dictWordRepository->create(
            [
                'word' => $wordStr,
                'language_id' => $language->getId(),
                'response' => '{"head":{},"def":[]}',
                'pos' => null,
            ]
        );
    }
}
