<?php

namespace App\Services;

use App\External\YandexDict;
use App\Models\Language;
use App\Models\YandexDictWord;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;
use Webmozart\Assert\Assert;

class YandexDictService implements ExternalDictServiceInterface
{
    private DictWordRepositoryInterface $dictWordRepository;
    private YandexDict $yandexDict;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        YandexDict $yandexDict
    )
    {
        $this->dictWordRepository = $dictWordRepository;
        $this->yandexDict = $yandexDict;
    }

    public function loadFromDictionary(
        Language $language,
        string $wordStr
    ): ?YandexDictWord
    {
        Assert::notNull($language->yandexDictCode);

        $result = $this->yandexDict->request(
            $language->yandexDictCode,
            $wordStr
        );

        if (strlen($result) == 0) {
            return null;
        }

        $dictWord = $this->dictWordRepository->create(
            [
                'word' => $wordStr,
                'language_id' => $language->getId(),
                'response' => $result,
            ]
        );

        $data = $this->parseApiResult($result);
        $dictWord = $this->applyParsedData($dictWord, $data);

        return $dictWord;
    }

    private function parseApiResult(?string $result): ?array
    {
        return strlen($result) > 0
            ? json_decode($result, true)
            : null;
    }

    private function applyParsedData(
        YandexDictWord $dictWord,
        ?array $data
    ): YandexDictWord
    {
        if (is_array($data)) {
            $def = $data['def'][0] ?? null;
            $pos = $def['pos'] ?? null;

            $dictWord->pos = $pos;
        }

        return $dictWord;
    }
}
